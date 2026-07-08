<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    require_once __DIR__ . "/Booking.php";
    $db = Database::getConnection();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $role = $_SESSION["role"] ?? "user";
    $userId = (int)$_SESSION["id"];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="onix-card-wide p-4 w-100">

            <h2 class="text-center fw-bold mb-4">
                <?= ($role === "user") ? "Mis reservas" : "Gestión de reservas" ?>
            </h2>

            <!-- Messages -->
            <div class="mb-3">
                <?php

                    if (isset($_GET["updated_booking"])) {
                        echo "<p class='alert alert-success'>La reserva se ha actualizado correctamente.</p>";
                    }
                    if (isset($_GET["cancelled_booking"])) {
                        echo "<p class='alert alert-warning'>La reserva ha sido cancelada.</p>";
                    }

                    if (isset($_GET["error"])) {
                        $error = htmlspecialchars($_GET["error"]);

                        if ($error === "invalid_status") {
                            echo "<p class='alert alert-danger text-center mb-4'>Ese estado no es válido.</p>";
                        }
                        if ($error === "booking_not_found") {
                            echo "<p class='alert alert-danger text-center mb-4'>La reserva no existe.</p>";
                        }
                        if ($error === "unauthorized") {
                            echo "<p class='alert alert-danger text-center mb-4'>No tienes permiso para realizar esta acción.</p>";
                        }
                        if ($error === "not_cancellable") {
                            echo "<p class='alert alert-danger text-center mb-4'>La reserva ya no es cancelable.</p>";
                        }
                        if ($error === "not_editable") {
                            echo "<p class='alert alert-danger text-center mb-4'>La reserva ya no es editable.</p>";
                        }
                    }

                ?>
            </div>

            <form method="get" action="/bookings/booking_list.php" class="mb-4 onix-search mx-auto">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por fecha" autocomplete="off"
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-onix fw-semibold" type="submit">Buscar</button>
                    <?php if (isset($_GET['only_mine'])): ?>
                        <input type="hidden" name="only_mine" value="1">
                    <?php endif; ?>
                </div>
            </form>

            <?php

                // Pagination and sorting settings
                $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                $perPage = 5;

                $allowedColumns = [
                    "id" => "r.id",
                    "date" => "r.fecha",
                    "time" => "r.hora",
                    "people" => "r.num_personas",
                    "status" => "r.estado",
                    "user_name" => "u.nombre"
                ];

                $sortBy = $_GET["sort_by"] ?? "date";
                $orderField = $allowedColumns[$sortBy] ?? "r.fecha";

                $sortDir = strtoupper($_GET["sort_dir"] ?? "DESC");
                if (!in_array($sortDir, ["ASC", "DESC"])) $sortDir = "DESC";

                // Filter logic depending on role
                $onlyUser = null;
                if ($role === "user" || (isset($_GET["only_mine"]) && $_GET["only_mine"] == 1)) {
                    $onlyUser = $userId;
                }

                // Apply filter + sort + pagination if search exists
                if (isset($_GET["search"]) && trim($_GET["search"]) !== "") {

                    $searchTerm = "%" . trim($_GET["search"]) . "%";

                    // Count filtered results
                    if ($onlyUser !== null) {
                        $countQuery = $db->prepare("SELECT COUNT(*) AS total FROM reservas WHERE id_usuario = :user_id AND (fecha LIKE :search)");
                        $countQuery->execute([
                            ":user_id" => $userId,
                            ":search" => $searchTerm
                        ]);
                    } else {
                        $countQuery = $db->prepare("SELECT COUNT(*) AS total FROM reservas WHERE (fecha LIKE :search)");
                        $countQuery->execute([":search" => $searchTerm]);
                    }

                    $totalReservations = $countQuery->fetch(PDO::FETCH_ASSOC)["total"];
                    $totalPages = ceil($totalReservations / $perPage);
                    $offset = ($page - 1) * $perPage;

                    $where = "WHERE r.fecha LIKE :search";
                    if ($onlyUser !== null) {
                        $where = "WHERE r.id_usuario = :user_id AND r.fecha LIKE :search";
                    }

                    // Obtain filtered results manually with exact aliases
                    $query = $db->prepare("SELECT r.id, r.id_usuario AS user_id, r.fecha AS date, r.hora AS time, r.num_personas AS people, r.estado AS status, u.nombre AS user_name 
                                           FROM reservas r INNER JOIN usuarios u ON r.id_usuario = u.id $where ORDER BY $orderField $sortDir LIMIT :offset, :limit");

                    $query->bindValue(":search", $searchTerm, PDO::PARAM_STR);
                    $query->bindValue(":offset", $offset, PDO::PARAM_INT);
                    $query->bindValue(":limit", $perPage, PDO::PARAM_INT);
                    if ($onlyUser !== null) {
                        $query->bindValue(":user_id", $userId, PDO::PARAM_INT);
                    }
                    $query->execute();

                    $query->setFetchMode(PDO::FETCH_CLASS, "Booking");
                    $reservations = $query->fetchAll();

                } else {

                    // Standard fetch using Booking class static method
                    $reservations = Booking::getBookings($page, $perPage, $sortBy, $sortDir, $onlyUser);

                    // Total number of reservations for pagination
                    if ($onlyUser !== null) {
                        $countQuery = $db->prepare("SELECT COUNT(*) AS total FROM reservas WHERE id_usuario = :user_id");
                        $countQuery->execute([":user_id" => $userId]);
                    } else {
                        $countQuery = $db->query("SELECT COUNT(*) AS total FROM reservas");
                    }

                    $totalReservations = $countQuery->fetch(PDO::FETCH_ASSOC)["total"];
                    $totalPages = ceil($totalReservations / $perPage);
                }

                // Sorting icons helper
                function sortIcon(string $col, string $currentSortBy, string $currentSortDir): string {
                    if ($col !== $currentSortBy) return '<i class="bi bi-arrow-down-up"></i>';
                    return $currentSortDir === "ASC"
                        ? '<i class="bi bi-arrow-up"></i>'
                        : '<i class="bi bi-arrow-down"></i>';
                }

                // Sort URL builder
                function sortUrl(string $col, string $currentSortDir): string {
                    $newDir = $currentSortDir === "ASC" ? "DESC" : "ASC";
                    $url = "/bookings/booking_list.php?sort_by=$col&sort_dir=$newDir";
                    
                    if (isset($_GET["search"])) {
                        $url .= "&search=" . urlencode($_GET["search"]);
                    }
                    if (isset($_GET["only_mine"])) { 
                        $url .= "&only_mine=1"; 
                    }
                    return $url;
                }

            ?>

            <div class="table-responsive">
                <table class="onix-table align-middle text-center mx-auto">
                    <thead>
                        <tr>
                            <th><a href="<?= sortUrl('id', $sortDir) ?>">ID <?= sortIcon('id', $sortBy, $sortDir) ?></a></th>
                            <th><a href="<?= sortUrl('date', $sortDir) ?>">Fecha <?= sortIcon('date', $sortBy, $sortDir) ?></a></th>
                            <th><a href="<?= sortUrl('time', $sortDir) ?>">Hora <?= sortIcon('time', $sortBy, $sortDir) ?></a></th>
                            <th><a href="<?= sortUrl('people', $sortDir) ?>">Personas <?= sortIcon('people', $sortBy, $sortDir) ?></a></th>
                            <th><a href="<?= sortUrl('status', $sortDir) ?>">Estado <?= sortIcon('status', $sortBy, $sortDir) ?></a></th>

                            <?php if ($role !== "user"): ?>
                                <th><a href="<?= sortUrl('user_name', $sortDir) ?>">Usuario <?= sortIcon('user_name', $sortBy, $sortDir) ?></a></th>
                            <?php endif; ?> 

                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php

                            if (empty($reservations)) {
                                echo "<tr><td colspan='7' class='text-center py-3 text-light'>No se encontraron reservas</td></tr>";
                            } else {
                                foreach ($reservations as $r) {

                                    $reservationDateStr = $r->getDate();
                                    $reservationDate = strtotime($reservationDateStr);
                                    $today = strtotime(date("Y-m-d"));
                                    $status = $r->getStatus();
                                    $id = $r->getId();
                                    $reservationHour = $r->getTime();
                                    $currentHour = date("H:i");

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars((string)$id) . "</td>";
                                    echo "<td>" . date("d/m/Y", $reservationDate) . "</td>";
                                    echo "<td>" . htmlspecialchars((string)$reservationHour) . "</td>";
                                    echo "<td>" . htmlspecialchars((string)$r->getPeople()) . "</td>";
                                    echo "<td>" . htmlspecialchars((string)$status) . "</td>";

                                    if ($role !== "user") {
                                        echo "<td>" . htmlspecialchars((string)$r->getUserName()) . "</td>";
                                    }

                                    echo "<td>";

                                        // If the date has already passed, there is nothing anyone can do
                                        if ($reservationDate < $today) {
                                            echo "<span class='text-light'>—</span>";
                                        }
                                        // If reservation is today, check if the hour has already passed
                                        elseif ($reservationDate == $today && $reservationHour <= $currentHour) {
                                            echo "<span class='text-light'>—</span>";
                                        }
                                        // Actions for users (or admins viewing their own reservations)
                                        elseif ($role === "user" || (isset($_GET["only_mine"]) && $_GET["only_mine"] == 1)) {

                                            if ($status === "pendiente") {
                                                // Can edit
                                                echo "<a href='/bookings/booking_edit.php?id=$id' class='text-onix fw-bold'>
                                                        <i class='bi bi-pencil-square'></i>
                                                      </a>";
                                            }
                                            elseif ($status === "confirmada") {
                                                // Can cancel
                                                echo "<a href='/bookings/booking_cancel.php?id=$id' 
                                                        class='text-danger fw-bold'
                                                        onclick='return confirm(\"¿Seguro que quieres cancelar esta reserva?\");'>
                                                        <i class='bi bi-x-circle'></i>
                                                      </a>";
                                            }
                                            else {
                                                // Cancelled status cannot be changed by standard user
                                                echo "<span class='text-light'>—</span>";
                                            }
                                        }
                                        // Actions for administrators managing others
                                        else {
                                            if ($status === "cancelada") {
                                                echo "<span class='text-light'>—</span>";
                                            } else {
                                                // If date hasn't passed and not cancelled, admin can edit
                                                echo "<a href='/bookings/booking_edit.php?id=$id' class='text-onix fw-bold'>
                                                        <i class='bi bi-pencil-square'></i>
                                                      </a>";
                                            }
                                        }

                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="text-center mt-4">
                <?php
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $url = "/bookings/booking_list.php?page=$i&sort_by=$sortBy&sort_dir=$sortDir";
                        
                        if (isset($_GET["search"])) {
                            $url .= "&search=" . urlencode($_GET["search"]);
                        }
                        if (isset($_GET["only_mine"])) { 
                            $url .= "&only_mine=1"; 
                        }

                        if ($i == $page) {
                            echo "<button class='btn btn-onix mx-1'>$i</button>";
                        } else {
                            echo "<a href='$url' class='btn btn-outline-onix mx-1'>$i</a>";
                        }
                    }
                ?>
            </div>

            <div class="text-center text-lg-start mt-3">
                <?php
                    if ($role === "user") {
                        $returnUrl = "/users/profile.php";
                    } elseif (isset($_GET["only_mine"]) && $_GET["only_mine"] == 1) {
                        $returnUrl = "/users/profile.php";
                    } else {
                        $returnUrl = "/utils/admin_panel.php";
                    }
                ?>
                <a href="<?= $returnUrl ?>" class="btn btn-outline-secondary">Volver</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
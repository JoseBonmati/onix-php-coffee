<?php

    require_once "../utilidades/conectar_db.php";
    require_once "Reserve.php";
    $con = conectar();
    session_start();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    $role = $_SESSION["rol"];
    $userId = $_SESSION["id"];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="onix-card-wide p-4 w-100">

            <h2 class="text-center fw-bold mb-4">
                <?= ($role === "usuario") ? "Mis reservas" : "Gestión de reservas" ?>
            </h2>

            <!-- Messages -->
            <div class="mb-3">
                <?php

                    if (isset($_GET["reservaEditada"])) {
                        echo "<p class='alert alert-success'>La reserva se ha actualizado correctamente.</p>";
                    }
                    if (isset($_GET["reservaCancelada"])) {
                        echo "<p class='alert alert-warning'>La reserva ha sido cancelada.</p>";
                    }

                    if (isset($_GET["error"])) {
                        $error = htmlspecialchars($_GET["error"]);

                        if ($error === "estadoInvalido") {
                            echo "<p class='alert alert-danger text-center mb-4'>Ese estado no es válido.</p>";
                        }
                        if ($error === "reservaNoExiste") {
                            echo "<p class='alert alert-danger text-center mb-4'>La reserva no existe.</p>";
                        }
                        if ($error === "noAutorizado") {
                            echo "<p class='alert alert-danger text-center mb-4'>No tienes permiso para realizar esta acción.</p>";
                        }
                        if ($error === "noCancelable") {
                            echo "<p class='alert alert-danger text-center mb-4'>La reserva ya no es cancelable.</p>";
                        }
                        if ($error === "noEditable") {
                            echo "<p class='alert alert-danger text-center mb-4'>La reserva ya no es editable.</p>";
                        }
                    }

                ?>
            </div>

            <form method="get" action="reservaConsulta.php" class="mb-4 onix-search mx-auto">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por fecha" autocomplete="off"
                           value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                    <button class="btn btn-onix fw-semibold" type="submit">Buscar</button>
                </div>
            </form>

            <?php

                // Pagination and sorting settings
                $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                $resultsPP = 5;

                $allowedColumns = ["r.id", "r.fecha", "r.hora", "r.num_personas", "r.estado", "u.nombre"];
                $order = $_GET["order"] ?? "r.fecha";
                if (!in_array($order, $allowedColumns)) $order = "r.fecha";

                $orderType = strtoupper($_GET["orderType"] ?? "DESC");
                if (!in_array($orderType, ["ASC", "DESC"])) $orderType = "DESC";

                // If you are a normal user, only your reservations
                // If you are an admin and ?soloMios=1 is included, only your reservations
                if ($role === "usuario") {
                    $onlyUser = $userId;
                } elseif (isset($_GET["onlyMine"]) && $_GET["onlyMine"] == 1) {
                    $onlyUser = $userId;
                } else {
                    $onlyUser = null;
                }

                // If there is a search, we apply filter + order + pagination
                if (isset($_GET["buscar"]) && trim($_GET["buscar"]) !== "") {

                    $busqueda = "%" . trim($_GET["buscar"]) . "%";

                    // Count filtered results
                    if ($onlyUser !== null) {
                        $countQuery = $con->prepare("SELECT COUNT(*) AS total FROM reservas WHERE id_usuario = :id AND (fecha LIKE :busqueda)");
                        $countQuery->execute([
                            ":id" => $userId,
                            ":busqueda" => $busqueda
                        ]);
                    } else {
                        $countQuery = $con->prepare("SELECT COUNT(*) AS total FROM reservas WHERE (fecha LIKE :busqueda)");
                        $countQuery->execute([":busqueda" => $busqueda]);
                    }

                    $totalReservations = $countQuery->fetch()["total"];

                    $totalPages = ceil($totalReservations / $resultsPP);
                    $start = ($page - 1) * $resultsPP;

                    $where = "WHERE r.fecha LIKE :busqueda";

                    if ($onlyUser !== null) {
                        $where = "WHERE r.id_usuario = :idUsuario AND r.fecha LIKE :busqueda";
                    }

                    // Obtain filtered results with pagination and sorting
                    $query = $con->prepare("SELECT r.id, r.id_usuario AS user_id, r.fecha AS date, r.hora AS time, r.num_personas AS people, r.estado AS status, u.nombre AS userName 
                                            FROM reservas r JOIN usuarios u ON r.id_usuario = u.id $where ORDER BY $order $orderType LIMIT :inicio, :resultados");

                    $query->bindValue(":busqueda", $busqueda, PDO::PARAM_STR);
                    $query->bindValue(":inicio", $start, PDO::PARAM_INT);
                    $query->bindValue(":resultados", $resultsPP, PDO::PARAM_INT);
                    if ($onlyUser !== null) $query->bindValue(":idUsuario", $userId, PDO::PARAM_INT);
                    $query->execute();

                    $query->setFetchMode(PDO::FETCH_CLASS, "Reserve");
                    $reservations = $query->fetchAll();

                } else {

                    // Obtain reservations
                    $reservations = obtenerReservas($con, $page, $resultsPP, $order, $orderType, $onlyUser);

                    // Total number of reservations
                    if ($onlyUser !== null) {
                        $count = $con->prepare("SELECT COUNT(*) AS total FROM reservas WHERE id_usuario = :id");
                        $count->execute([":id" => $userId]);
                    } else {
                        $count = $con->prepare("SELECT COUNT(*) AS total FROM reservas");
                        $count->execute();
                    }

                    $totalReservations = $count->fetch()["total"];
                    $totalPages = ceil($totalReservations / $resultsPP);
                }

                // Sorting icons
                function iconoOrden($col, $order, $orderType) {
                    if ($col !== $order) return '<i class="bi bi-arrow-down-up"></i>';
                    return $orderType === "ASC"
                        ? '<i class="bi bi-arrow-up"></i>'
                        : '<i class="bi bi-arrow-down"></i>';
                }

                function urlOrden($col, $orderType) {
                        $newType = $orderType === "ASC" ? "DESC" : "ASC";
                        $url = "reservaConsulta.php?order=$col&orderType=$newType";
                        if (isset($_GET["buscar"])) {
                            $url .= "&buscar=" . urlencode($_GET["buscar"]);
                        }
                        if (isset($_GET["onlyMine"])) { 
                            $url .= "&onlyMine=1"; 
                        }
                        return $url;
                    }

            ?>

            <div class="table-responsive">
                <table class="onix-table align-middle text-center mx-auto">
                    <thead>
                        <tr>
                            <th><a href="<?= urlOrden('r.id', $orderType) ?>">ID <?= iconoOrden('r.id', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('r.fecha', $orderType) ?>">Fecha <?= iconoOrden('r.fecha', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('r.hora', $orderType) ?>">Hora <?= iconoOrden('r.hora', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('r.num_personas', $orderType) ?>">Personas <?= iconoOrden('r.num_personas', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('r.estado', $orderType) ?>">Estado <?= iconoOrden('r.estado', $order, $orderType) ?></a></th>

                            <?php if ($role !== "usuario"): ?>
                                <th><a href="<?= urlOrden('u.nombre', $orderType) ?>">Usuario <?= iconoOrden('u.nombre', $order, $orderType) ?></a></th>
                            <?php endif; ?> 

                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php

                            if (empty($reservations)) {
                                echo "<tr><td colspan='10' class='text-center py-3 text-light'>
                                        No se encontraron reservas
                                    </td></tr>";
                            } else {
                                foreach ($reservations as $r) {

                                    $reservationDate = strtotime($r->getFecha());
                                    $today = strtotime(date("Y-m-d"));
                                    $status = $r->getEstado();
                                    $id = (int)$r->getId();
                                    $reservationHour = $r->getHora();
                                    $currentHour = date("H:i");

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($id) . "</td>";
                                    echo "<td>" . date("d/m/Y", $reservationDate) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservationHour) . "</td>";
                                    echo "<td>" . htmlspecialchars($r->getPersonas()) . "</td>";
                                    echo "<td>" . htmlspecialchars($status) . "</td>";

                                    if ($role !== "usuario") {
                                        echo "<td>" . htmlspecialchars($r->getUsuarioNombre()) . "</td>";
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
                                        // Actions for users
                                        elseif ($role === "usuario" || (isset($_GET["onlyMine"]) && $_GET["onlyMine"] == 1)) {

                                            if ($status === "pendiente") {
                                                // Can edit
                                                echo "<a href='reservaEditar.php?id=$id' class='text-onix fw-bold'>
                                                        <i class='bi bi-pencil-square'></i>
                                                    </a>";
                                            }
                                            elseif ($status === "confirmada") {
                                                // Can cancel
                                                echo "<a href='reservaCancelar.php?id=$id' 
                                                        class='text-danger fw-bold'
                                                        onclick='return confirm(\"¿Seguro que quieres cancelar esta reserva?\");'>
                                                        <i class='bi bi-x-circle'></i>
                                                    </a>";
                                            }
                                            else {
                                                // Reserves with cancelled status cannot be changed
                                                echo "<span class='text-light'>—</span>";
                                            }
                                        }
                                        // Actions for administrators
                                        else {
                                            if ($status === "cancelada") {
                                                echo "<span class='text-light'>—</span>";
                                            } else {
                                                // If the date hasn't already passed, you can always edit
                                                echo "<a href='reservaEditar.php?id=$id' class='text-onix fw-bold'>
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
                        $url = "reservaConsulta.php?page=$i&order=$order&orderType=$orderType";
                        if (isset($_GET["buscar"])) {
                            $url .= "&buscar=" . urlencode($_GET["buscar"]);
                        }
                        if (isset($_GET["onlyMine"])) { 
                            $url .= "&onlyMine=1"; 
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
                    if ($role === "usuario") {
                        $return = "../usuarios/perfil.php";
                    } elseif (isset($_GET["onlyMine"]) && $_GET["onlyMine"] == 1) {
                        $return = "../usuarios/perfil.php";
                    } else {
                        $return = "../utilidades/panelAdministrador.php";
                    }
                ?>
                <a href="<?= $return ?>" class="btn btn-outline-secondary">Volver</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

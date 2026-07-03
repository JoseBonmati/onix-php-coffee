<?php

    require_once "../utilidades/conectar_db.php";
    require_once "User.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can view this page
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="onix-card-wide p-4 w-100">
            <h2 class="text-center fw-bold mb-4">Gestión de Usuarios</h2>

            <!-- Success messages -->
            <div class="mb-3">
                <?php

                    if (isset($_GET["nameE"]) && isset($_GET["emailE"])) {
                        $nameE = htmlspecialchars($_GET["nameE"]);
                        $emailE = htmlspecialchars($_GET["emailE"]);
                        echo "<p class='alert alert-success'>El usuario <b>$nameE</b> con email <b>$emailE</b> ha sido modificado correctamente.</p>";
                    }
                    if (isset($_GET["nameN"]) && isset($_GET["emailN"])) {
                        $nameN = htmlspecialchars($_GET["nameN"]);
                        $emailN = htmlspecialchars($_GET["emailN"]);
                        echo "<p class='alert alert-success'>El usuario <b>$nameN</b> con email <b>$emailN</b> se ha creado correctamente.</p>";
                    }
                    if (isset($_GET["nameD"])) {
                        $nameD = htmlspecialchars($_GET["nameD"]);
                        echo "<p class='alert alert-success'>El usuario <b>$nameD</b> se ha eliminado correctamente.</p>";
                    }

                ?>
            </div>

            <!-- Search bar -->
            <form method="get" action="usuarioConsulta.php" class="mb-4 onix-search mx-auto">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o email..." autocomplete="off"
                           value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                    <button class="btn btn-onix fw-semibold" type="submit">Buscar</button>
                </div>
            </form>

            <?php

                // Pagination and sorting setup
                $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                $resultsPP = 5;

                $allowedColumns = ["id","nombre","email","telefono","rol","estado"];
                $order = isset($_GET["order"]) ? $_GET["order"] : "nombre";
                if (!in_array($order, $allowedColumns)) {
                    $order = "nombre";
                }

                $orderType = isset($_GET["orderType"]) ? strtoupper($_GET["orderType"]) : "ASC";
                if (!in_array($orderType, ["ASC", "DESC"])) {
                    $orderType = "ASC";
                }

                // If there is a search, we apply filter + order + pagination
                if (isset($_GET["buscar"]) && trim($_GET["buscar"]) !== "") {

                    $busqueda = "%" . trim($_GET["buscar"]) . "%";

                    // Count filtered results
                    $countQuery = $con->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE nombre LIKE :busqueda OR email LIKE :busqueda");
                    $countQuery->execute([":busqueda" => $busqueda]);
                    $totalUsers = $countQuery->fetch()["total"];

                    $totalPages = ceil($totalUsers / $resultsPP);
                    $start = ($page - 1) * $resultsPP;

                    // Obtain filtered results with pagination and sorting
                    $query = $con->prepare("SELECT id, nombre AS name, email, contrasenya AS password, telefono AS phone, rol AS role, estado AS status FROM usuarios
                                            WHERE nombre LIKE :busqueda OR email LIKE :busqueda ORDER BY $order $orderType LIMIT :inicio, :resultados");

                    $query->bindValue(":busqueda", $busqueda, PDO::PARAM_STR);
                    $query->bindValue(":inicio", $start, PDO::PARAM_INT);
                    $query->bindValue(":resultados", $resultsPP, PDO::PARAM_INT);
                    $query->execute();

                    $query->setFetchMode(PDO::FETCH_CLASS, "User");
                    $users = $query->fetchAll();

                } else {

                    // Query users with pagination and sorting
                    $users = obtenerUsuarios($con, $page, $resultsPP, $order, $orderType);

                    $query = $con->prepare("SELECT count(*) AS total FROM usuarios");
                    $query->execute();
                    $row = $query->fetch();
                    $totalUsers = $row["total"];

                    $totalPages = ceil($totalUsers / $resultsPP);
                }

                //Sorting icons
                function iconoOrden($col, $order, $orderType) {
                    if ($col !== $order) return '<i class="bi bi-arrow-down-up"></i>';
                    return $orderType === "ASC"
                        ? '<i class="bi bi-arrow-up"></i>'
                        : '<i class="bi bi-arrow-down"></i>';
                }

                function urlOrden($col, $orderType) {
                    $newType = $orderType === "ASC" ? "DESC" : "ASC";
                    $url = "usuarioConsulta.php?order=$col&orderType=$newType";
                    if (isset($_GET["buscar"])) {
                        $url .= "&buscar=" . urlencode($_GET["buscar"]);
                    }
                    return $url;
                }

            ?>

            <div class="table-responsive">
                <table class="onix-table align-middle text-center mx-auto">
                    <thead>
                        <tr>
                            <th><a href="<?= urlOrden('id', $orderType) ?>">ID <?= iconoOrden('id', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('nombre', $orderType) ?>">Nombre <?= iconoOrden('nombre', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('email', $orderType) ?>">Email <?= iconoOrden('email', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('telefono', $orderType) ?>">Teléfono <?= iconoOrden('telefono', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('rol', $orderType) ?>">Rol <?= iconoOrden('rol', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('estado', $orderType) ?>">Estado <?= iconoOrden('estado', $order, $orderType) ?></a></th>
                            <th>Editar</th>
                            <th>Borrar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            if (empty($users)) {
                                echo "<tr><td colspan='8' class='text-center py-3 text-light'>
                                          No se encontraron usuarios
                                      </td></tr>";
                            } else {
                                foreach ($users as $user) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($user->getId()) . "</td>";
                                    echo "<td>" . htmlspecialchars($user->getNombre()) . "</td>";
                                    echo "<td>" . htmlspecialchars($user->getEmail()) . "</td>";
                                    echo "<td>" . htmlspecialchars($user->getTelefono()) . "</td>";
                                    echo "<td>" . htmlspecialchars($user->getRol()) . "</td>";
                                    echo "<td>" . htmlspecialchars($user->getEstado()) . "</td>";

                                    if ($_SESSION["id"] == $user->getId()) {
                                        echo "<td><i class='bi bi-ban text-light'></i></td>"; 
                                        echo "<td><i class='bi bi-ban text-light'></i></td>";
                                    } else {
                                        echo "<td><a class='text-onix fw-bold' href='usuarioEditar.php?id=" . $user->getId() . "'>
                                                <i class='bi bi-pencil-square'></i>
                                            </a></td>";

                                        echo "<td><a class='text-danger fw-bold' href='usuarioEliminar.php?id=" . $user->getId() . "'>
                                                <i class='bi bi-trash-fill'></i>
                                            </a></td>";
                                    }
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
                        $url = "usuarioConsulta.php?page=$i&order=$order&orderType=$orderType";
                        if (isset($_GET["buscar"])) {
                            $url .= "&buscar=" . urlencode($_GET["buscar"]);
                        }
                        if ($i == $page) {
                            echo "<button class='btn btn-onix mx-1'>$i</button>";
                        } else {
                            echo "<a href='$url' class='btn btn-outline-onix mx-1'>$i</a>";
                        }
                    }

                ?>
            </div>

            <div class="text-center mt-4 d-flex justify-content-center">
                <a href="usuarioCrear.php" class="btn btn-onix">Nuevo usuario</a>
            </div>

            <div class="text-center text-lg-start mt-3">
                <a href="../utilidades/panelAdministrador.php" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
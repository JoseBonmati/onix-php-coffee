<?php

    require_once "../utilidades/conectar_db.php";
    require_once "Category.php";
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
    <title>Gestión de Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="onix-card-wide p-4 w-100">
            <h2 class="text-center fw-bold mb-4">Gestión de Categorías</h2>

            <!-- Success messages -->
            <div class="mb-3">
                <?php

                    if (isset($_GET["nameE"])) {
                        $nameE = htmlspecialchars($_GET["nameE"]);
                        echo "<p class='alert alert-success'>La categoría <b>$nameE</b> ha sido modificada correctamente.</p>";
                    }
                    if (isset($_GET["nameN"])) {
                        $nameN = htmlspecialchars($_GET["nameN"]);
                        echo "<p class='alert alert-success'>La categoría <b>$nameN</b> se ha creado correctamente.</p>";
                    }
                    if (isset($_GET["nameD"])) {
                        $nameD = htmlspecialchars($_GET["nameD"]);
                        echo "<p class='alert alert-success'>La categoría <b>$nameD</b> se ha eliminado correctamente.</p>";
                    }

                ?>
            </div>

            <!-- Search bar -->
            <form method="get" action="categoriaConsulta.php" class="mb-4 onix-search mx-auto">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..." autocomplete="off"
                           value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                    <button class="btn btn-onix fw-semibold" type="submit">Buscar</button>
                </div>
            </form>

            <?php

                // Pagination and sorting setup
                $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                $resultsPP = 5;

                $allowedColumns = ["id", "nombre", "estado"];
                $order = isset($_GET["order"]) ? $_GET["order"] : "nombre";
                if (!in_array($order, $allowedColumns)) $order = "nombre";

                $orderType = isset($_GET["orderType"]) ? strtoupper($_GET["orderType"]) : "ASC";
                if (!in_array($orderType, ["ASC", "DESC"])) $orderType = "ASC";

                // If there is a search, we apply filter + order + pagination
                if (isset($_GET["buscar"]) && trim($_GET["buscar"]) !== "") {

                    $busqueda = "%" . trim($_GET["buscar"]) . "%";

                    // Count filtered results
                    $countQuery = $con->prepare("SELECT COUNT(*) AS total FROM categorias WHERE nombre LIKE :busqueda");
                    $countQuery->execute([":busqueda" => $busqueda]);
                    $totalCategories = $countQuery->fetch()["total"];

                    $totalPages = ceil($totalCategories / $resultsPP);
                    $start = ($page - 1) * $resultsPP;

                    // Obtain filtered results with pagination and sorting
                    $query = $con->prepare("SELECT id, nombre AS name, estado AS status FROM categorias WHERE nombre LIKE :busqueda ORDER BY $order $orderType LIMIT :inicio, :resultados");

                    $query->bindValue(":busqueda", $busqueda, PDO::PARAM_STR);
                    $query->bindValue(":inicio", $start, PDO::PARAM_INT);
                    $query->bindValue(":resultados", $resultsPP, PDO::PARAM_INT);
                    $query->execute();

                    $query->setFetchMode(PDO::FETCH_CLASS, "Category");
                    $categories = $query->fetchAll();

                } else {

                    // Query categories normally
                    $categories = obtenerCategorias($con, $page, $resultsPP, $order, $orderType);

                    $query = $con->prepare("SELECT count(*) AS total FROM categorias");
                    $query->execute();
                    $row = $query->fetch();
                    $totalCategories = $row["total"];

                    $totalPages = ceil($totalCategories / $resultsPP);
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
                    $url = "categoriaConsulta.php?order=$col&orderType=$newType";
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
                            <th><a href="<?= urlOrden('estado', $orderType) ?>">Estado <?= iconoOrden('estado', $order, $orderType) ?></a></th>
                            <th>Editar</th>
                            <th>Borrar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            if (empty($categories)) {
                                echo "<tr><td colspan='5' class='text-center py-3 text-light'>
                                          No se encontraron categorías
                                      </td></tr>";
                            } else {
                                foreach ($categories as $category) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($category->getId()) . "</td>";
                                    echo "<td>" . htmlspecialchars($category->getNombre()) . "</td>";
                                    echo "<td>" . htmlspecialchars($category->getEstado()) . "</td>";
                                    echo "<td><a class='text-onix fw-bold' href='categoriaEditar.php?id=" . $category->getId() . "'>
                                              <i class='bi bi-pencil-square'></i>
                                          </a></td>";

                                    echo "<td><a class='text-danger fw-bold' href='categoriaEliminar.php?id=" . $category->getId() . "'>
                                              <i class='bi bi-trash-fill'></i>
                                          </a></td>";

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
                        $url = "categoriaConsulta.php?page=$i&order=$order&orderType=$orderType";
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
                <a href="categoriaCrear.php" class="btn btn-onix">Nueva categoría</a>
            </div>

            <div class="text-center text-lg-start mt-3">
                <a href="../utilidades/panelAdministrador.php" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
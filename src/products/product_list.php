<?php

    require_once "../utilidades/conectar_db.php";
    require_once "Product.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators
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
    <title>Consulta de productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="onix-card-wide p-4 w-100">
            <h2 class="text-center fw-bold mb-4">Gestión de Productos</h2>

            <!-- Success messages -->
            <div class="mb-3">
                <?php

                    if (isset($_GET["nameE"])) {
                        $nameE = htmlspecialchars($_GET["nameE"]);
                        echo "<p class='alert alert-success'>El producto <b>$nameE</b> ha sido modificado correctamente.</p>";
                    }
                    if (isset($_GET["nameN"])) {
                        $nameN = htmlspecialchars($_GET["nameN"]);
                        echo "<p class='alert alert-success'>El producto <b>$nameN</b> se ha creado correctamente.</p>";
                    }
                    if (isset($_GET["nameD"])) {
                        $nameD = htmlspecialchars($_GET["nameD"]);
                        echo "<p class='alert alert-success'>El producto <b>$nameD</b> se ha eliminado correctamente.</p>";
                    }

                ?>
            </div>

            <!-- Search bar -->
            <form method="get" action="productoConsulta.php" class="mb-4 onix-search mx-auto">
                <div class="input-group">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o categoria" autocomplete="off"
                           value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                    <button class="btn btn-onix fw-semibold" type="submit">Buscar</button>
                </div>
            </form>

            <?php

                // Pagination and sorting setup
                $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
                $resultsPP = 5;

                $allowedColumns = ["p.id", "p.nombre", "p.descripcion", "p.precio", "c.nombre", "p.estado"];
                $order = $_GET["order"] ?? "p.nombre";
                if (!in_array($order, $allowedColumns)) $order = "p.nombre";

                $orderType = strtoupper($_GET["orderType"] ?? "ASC");
                if (!in_array($orderType, ["ASC", "DESC"])) $orderType = "ASC";

                // If there is a search, we apply filter + order + pagination
                if (isset($_GET["buscar"]) && trim($_GET["buscar"]) !== "") {

                    $busqueda = "%" . trim($_GET["buscar"]) . "%";

                    // Count filtered results
                    $countQuery = $con->prepare("SELECT COUNT(*) AS total FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id 
                                                 WHERE (p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda)");
                    $countQuery->execute([":busqueda" => $busqueda]);
                    $totalProducts = $countQuery->fetch()["total"];

                    $totalPages = ceil($totalProducts / $resultsPP);
                    $start = ($page - 1) * $resultsPP;

                    // Obtain filtered results with pagination and sorting
                    $query = $con->prepare("SELECT p.id, p.nombre AS name, p.descripcion AS description, p.precio AS price, c.nombre AS categoryName, p.imagen AS image, p.estado AS status 
                                            FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id WHERE (p.nombre LIKE :busqueda OR c.nombre LIKE :busqueda)
                                            ORDER BY $order $orderType LIMIT :inicio, :resultados");

                    $query->bindValue(":busqueda", $busqueda, PDO::PARAM_STR);
                    $query->bindValue(":inicio", $start, PDO::PARAM_INT);
                    $query->bindValue(":resultados", $resultsPP, PDO::PARAM_INT);
                    $query->execute();

                    $query->setFetchMode(PDO::FETCH_CLASS, "Product");
                    $products = $query->fetchAll();

                } else {

                    // Query categories normally
                    $products = obtenerProductos($con, $page, $resultsPP, $order, $orderType);

                    $query = $con->prepare("SELECT count(*) AS total FROM productos");
                    $query->execute();
                    $totalProducts = $query->fetch()["total"];

                    $totalPages = ceil($totalProducts / $resultsPP);
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
                    $url = "productoConsulta.php?order=$col&orderType=$newType";
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
                            <th><a href="<?= urlOrden('p.id', $orderType) ?>">ID <?= iconoOrden('p.id', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('p.nombre', $orderType) ?>">Nombre <?= iconoOrden('p.nombre', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('p.descripcion', $orderType) ?>">Descripción <?= iconoOrden('p.descripcion', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('p.precio', $orderType) ?>">Precio <?= iconoOrden('p.precio', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('c.nombre', $orderType) ?>">Categoría <?= iconoOrden('c.nombre', $order, $orderType) ?></a></th>
                            <th><a href="<?= urlOrden('p.estado', $orderType) ?>">Estado <?= iconoOrden('p.estado', $order, $orderType) ?></a></th>
                            <th>Imagen</th>
                            <th>Editar</th>
                            <th>Borrar</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php

                            if (empty($products)) {
                                echo "<tr><td colspan='9' class='text-center py-3 text-light'>
                                          No se encontraron productos
                                      </td></tr>";
                            } else {
                                foreach ($products as $product) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($product->getId()) . "</td>";
                                    echo "<td>" . htmlspecialchars($product->getNombre()) . "</td>";
                                    echo "<td>" . htmlspecialchars($product->getDescripcion()) . "</td>";
                                    echo "<td>" . htmlspecialchars($product->getPrecio()) . " €</td>";
                                    echo "<td>" . htmlspecialchars($product->getCategoriaNombre()) . "</td>";
                                    echo "<td>" . htmlspecialchars($product->getEstado()) . "</td>";
                                    echo "<td><img src='" . htmlspecialchars($product->getImagen()) . "' class='img-thumbnail' style='max-height: 120px;'></td>";

                                    echo "<td><a class='text-onix fw-bold' href='productoEditar.php?id=" . $product->getId() . "'>
                                              <i class='bi bi-pencil-square'></i>
                                          </a></td>";

                                    echo "<td><a class='text-danger fw-bold' href='productoEliminar.php?id=" . $product->getId() . "'>
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
                        $url = "productoConsulta.php?page=$i&order=$order&orderType=$orderType";
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
                <a href="productoCrear.php" class="btn btn-onix">Nuevo producto</a>
            </div>

            <div class="text-center text-lg-start mt-3">
                <a href="../utilidades/panelAdministrador.php" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

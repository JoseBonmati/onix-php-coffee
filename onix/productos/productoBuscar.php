<?php
    require_once "../utilidades/conectar_db.php";
    require_once "product.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can use this search
    if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Render one result row with action links
    function mostrarFila(Product $product) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($product->getId()) . "</td>";
        echo "<td>" . htmlspecialchars($product->getNombre()) . "</td>";
        echo "<td>" . htmlspecialchars($product->getDescripcion()) . "</td>";
        echo "<td>" . htmlspecialchars($product->getPrecio()) . "</td>";
        echo "<td>" . htmlspecialchars($product->getCategoriaNombre()) . "</td>";
        echo "<td><img src='" . htmlspecialchars($product->getImagen()) . "' width='100' height='200'></td>";

        $idEscaped = (int) $product->getId();

        echo "<td><a href='productoEditar.php?id={$idEscaped}'>📝</a></td>";
        echo "<td><a href='productoEliminar.php?id={$idEscaped}'>❌</a></td>";
        echo "</tr>";
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar productos</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>
    <h1>Búsqueda de productos</h1>
    <p>Introduce nombre o categoría para buscar:</p>

    <!-- Search form -->
    <form name="fBusqueda" method="post" action="productoBuscar.php">
        Nombre: <input type="text" name="nombreB" size="30" maxlength="100">
        <br><br>
        Categoría: <input type="text" name="categoriaB" size="30" maxlength="100">
        <br><br>
        <a href="productoConsulta.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Buscar" name="enviar"/>
    </form>

<?php
    // Handle search submission
    if (isset($_POST["enviar"])) {
        echo "<h2>Resultado de la búsqueda:</h2>";

        $nameQ = trim($_POST["nombreB"] ?? "");
        $catQ  = trim($_POST["categoriaB"] ?? "");

        if ($nameQ === "" && $catQ === "") {
            echo "<p style='color:red'><b>Debes introducir al menos nombre o categoría para comenzar la búsqueda.</b></p>";
        } else {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Imagen</th>
                    <th>Editar</th>
                    <th>Borrar</th>
            </tr>";

            // Prepare query based on provided field
            if ($nameQ !== "") {
                $query = $con->prepare("SELECT p.id, p.nombre AS name, p.descripcion AS description, p.precio AS price, c.nombre AS categoryName, p.imagen AS image
                                        FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id WHERE p.nombre LIKE :nombre");
                $query->execute([":nombre" => "%" . $nameQ . "%"]);
            } elseif ($catQ !== "") {
                $query = $con->prepare("SELECT p.id, p.nombre AS name, p.descripcion AS description, p.precio AS price, c.nombre AS categoryName, p.imagen AS image
                                        FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id WHERE c.nombre LIKE :categoria");
                $query->execute([":categoria" => "%" . $catQ . "%"]);
            }

            // Map results to Product objects
            $query->setFetchMode(PDO::FETCH_CLASS, "Product");
            $products = $query->fetchAll();

            if ($products) {
                foreach ($products as $product) {
                    mostrarFila($product);
                }
            } else {
                echo "<p style='color:red'><b>No existe ningún producto con esos datos.</b></p>";
            }

            echo "</table>";
        }
    }
?>
</body>
</html>

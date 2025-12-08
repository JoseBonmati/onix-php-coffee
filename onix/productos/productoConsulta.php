<?php
    require_once "../utilidades/conectar_db.php";
    require_once "product.php";
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
    <link rel="stylesheet" href="../estilos.css">
    <title>Consulta de productos</title>
</head>
<body>
    <h1>Consultas de productos</h1>

    <?php
        // Success messages after CRUD actions
        if (isset($_GET["nameE"])) {
            $nameE = htmlspecialchars($_GET["nameE"]);
            echo "<p style='color:green'><b>El producto $nameE ha sido modificado correctamente.</b></p>";
        }

        if (isset($_GET["nameN"])) {
            $nameN = htmlspecialchars($_GET["nameN"]);
            echo "<p style='color:green'><b>El producto $nameN se ha creado correctamente.</b></p>";
        }

        if (isset($_GET["nameD"])) {
            $nameD = htmlspecialchars($_GET["nameD"]);
            echo "<p style='color:green'><b>El producto $nameD se ha eliminado correctamente.</b></p>";
        }
    ?>

    <table>
        <tr>
            <!-- Table headers with sorting links -->
            <th><a href="productoConsulta.php?order=p.id&orderType=ASC">ID</a></th>
            <th><a href="productoConsulta.php?order=p.nombre&orderType=ASC">Nombre</a></th>
            <th><a href="productoConsulta.php?order=p.descripcion&orderType=ASC">Descripción</a></th>
            <th><a href="productoConsulta.php?order=p.precio&orderType=ASC">Precio</a></th>
            <th><a href="productoConsulta.php?order=c.nombre&orderType=ASC">Categoría</a></th>
            <th>Imagen</th>
            <th id="editar"><b>Editar</b></th>
            <th id="borrar"><b>Borrar</b></th>
        </tr>

        <?php
            // Pagination and sorting setup
            $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
            $resultsPP = 5;

            $allowedColumns = ["p.id","p.nombre","p.descripcion","p.precio","c.nombre"];
            $order = isset($_GET["order"]) ? $_GET["order"] : "p.nombre";
            if (!in_array($order, $allowedColumns)) {
                $order = "p.nombre";
            }

            $orderType = isset($_GET["orderType"]) ? strtoupper($_GET["orderType"]) : "ASC";
            if (!in_array($orderType, ["ASC", "DESC"])) {
                $orderType = "ASC";
            }

            $products = obtenerProductos($con, $page, $resultsPP, $order, $orderType);

            foreach ($products as $product) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($product->getId()) . "</td>";
                echo "<td>" . htmlspecialchars($product->getNombre()) . "</td>";
                echo "<td>" . htmlspecialchars($product->getDescripcion()) . "</td>";
                echo "<td>" . htmlspecialchars($product->getPrecio()) . "</td>";
                echo "<td>" . htmlspecialchars($product->getCategoriaNombre()) . "</td>";
                echo "<td><img src='" . htmlspecialchars($product->getImagen()) . "' width='100' height='200'></td>";
                echo "<td><a href='productoEditar.php?id=" . $product->getId() . "'> 📝 </a></td>";
                echo "<td><a href='productoEliminar.php?id=" . $product->getId() . "'> ❌ </a></td>";
                echo "</tr>";
            }
        ?>
    </table>

    <br><br>

    <?php
        // Pagination controls
        $query = $con->prepare("SELECT count(*) AS total FROM productos");
        $query->execute();
        $row = $query->fetch();
        $totalProducts = $row["total"];

        $totalPages = ceil($totalProducts / $resultsPP);

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                echo "<button style='margin: 5px'><a style='text-decoration:none; color:black;'>$i</a></button>";
            } else {
                echo "<button style='margin: 5px'><a style='text-decoration:none; color:black;' href='productoConsulta.php?page=$i&order=$order&orderType=$orderType'>$i</a></button>";
            }
        }

        echo "<br><br>";
        echo '<a href="productoConsulta.php?order='.$order.'&orderType=ASC" style="margin: 5px"><button>Orden ascendente</button></a>';
        echo '<a href="productoConsulta.php?order='.$order.'&orderType=DESC" style="margin: 5px"><button>Orden descendente</button></a>';
        echo "<br><br>";
    ?>

    <a href="../utilidades/panelAdministrador.php" style="margin: 5px"><button>Volver</button></a>
    <a href="productoCrear.php" style="margin: 5px"><button>Nuevo producto</button></a>
    <a href="productoBuscar.php" style="margin: 5px"><button>Buscar producto</button></a>
</body>
</html>

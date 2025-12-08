<?php
    require_once "../utilidades/conectar_db.php";
    require_once "category.php";
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
    <title>Consulta de categorías</title>
</head>
<body>
    <h1>Consultas de categorías</h1>

    <?php
        // Success messages after CRUD actions
        if (isset($_GET["nameE"])) {
            $nameE = htmlspecialchars($_GET["nameE"]);
            echo "<p style='color:green'><b>La categoría $nameE ha sido modificada correctamente.</b></p>";
        }

        if (isset($_GET["nameN"])) {
            $nameN = htmlspecialchars($_GET["nameN"]);
            echo "<p style='color:green'><b>La categoría $nameN se ha creado correctamente.</b></p>";
        }

        if (isset($_GET["nameD"])) {
            $nameD = htmlspecialchars($_GET["nameD"]);
            echo "<p style='color:green'><b>La categoría $nameD se ha eliminado correctamente.</b></p>";
        }
    ?>

    <table>
        <tr>
            <!-- Table headers with sorting links -->
            <th><a href="categoriaConsulta.php?order=id&orderType=ASC">ID</a></th>
            <th><a href="categoriaConsulta.php?order=nombre&orderType=ASC">Nombre</a></th>
            <th id="editar"><b>Editar</b></th>
            <th id="borrar"><b>Borrar</b></th>
        </tr>

        <?php
            // Pagination and sorting setup
            $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
            $resultsPP = 5;

            $allowedColumns = ["id","nombre"];
            $order = isset($_GET["order"]) ? $_GET["order"] : "nombre";
            if (!in_array($order, $allowedColumns)) {
                $order = "nombre";
            }

            $orderType = isset($_GET["orderType"]) ? strtoupper($_GET["orderType"]) : "ASC";
            if (!in_array($orderType, ["ASC", "DESC"])) {
                $orderType = "ASC";
            }

            // Usamos la función obtenerCategorias
            $categories = obtenerCategorias($con, $page, $resultsPP, $order, $orderType);

            // Render table rows
            foreach ($categories as $category) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($category->getId()) . "</td>";
                echo "<td>" . htmlspecialchars($category->getNombre()) . "</td>";
                echo "<td><a href='categoriaEditar.php?id=" . $category->getId() . "'> 📝 </a></td>";
                echo "<td><a href='categoriaEliminar.php?id=" . $category->getId() . "'> ❌ </a></td>";
                echo "</tr>";
            }
        ?>
    </table>

    <br><br>

    <?php
        // Pagination controls
        $query = $con->prepare("SELECT count(*) AS total FROM categorias");
        $query->execute();
        $row = $query->fetch();
        $totalCategories = $row["total"];

        $totalPages = ceil($totalCategories / $resultsPP);

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                echo "<button style='margin: 5px'><a style='text-decoration:none; color:black;'>$i</a></button>";
            } else {
                echo "<button style='margin: 5px'><a style='text-decoration:none; color:black;' href='categoriaConsulta.php?page=$i&order=$order&orderType=$orderType'>$i</a></button>";
            }
        }

        echo "<br><br>";
        echo '<a href="categoriaConsulta.php?order='.$order.'&orderType=ASC" style="margin: 5px"><button>Orden ascendente</button></a>';
        echo '<a href="categoriaConsulta.php?order='.$order.'&orderType=DESC" style="margin: 5px"><button>Orden descendente</button></a>';
        echo "<br><br>";
    ?>

    <a href="../utilidades/panelAdministrador.php" style="margin: 5px"><button>Volver</button></a>
    <a href="categoriaCrear.php" style="margin: 5px"><button>Nueva categoría</button></a>
    <a href="categoriaBuscar.php" style="margin: 5px"><button>Buscar categoría</button></a>
</body>
</html>

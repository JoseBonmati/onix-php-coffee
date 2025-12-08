<?php
    require_once "../utilidades/conectar_db.php";
    require_once "category.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can use this search
    if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    $errorMessages = [];

    // Render one result row with action links
    function mostrarFila(Category $category) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($category->getId()) . "</td>";
        echo "<td>" . htmlspecialchars($category->getNombre()) . "</td>";

        $idEscaped = (int) $category->getId();

        echo "<td><a href='categoriaEditar.php?id={$idEscaped}'>📝</a></td>";
        echo "<td><a href='categoriaEliminar.php?id={$idEscaped}'>❌</a></td>";
        echo "</tr>";
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar categorías</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>
    <h1>Búsqueda de categorías</h1>
    <p>Introduce nombre para buscar:</p>

    <!-- Search form -->
    <form name="fBusqueda" method="post" action="categoriaBuscar.php">
        Nombre: <input type="text" name="nombreB" size="30" maxlength="100">
        <br><br>
        <a href="categoriaConsulta.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Buscar" name="enviar"/>
    </form>

<?php
    // Handle search submission
    if (isset($_POST["enviar"])) {
        echo "<h2>Resultado de la búsqueda:</h2>";

        $nameQ = trim($_POST["nombreB"] ?? "");

        if ($nameQ === "") {
            echo "<p style='color:red'><b>Debes introducir al menos un nombre para comenzar la búsqueda.</b></p>";
        } else {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Editar</th>
                    <th>Borrar</th>
            </tr>";

            // Prepare query with alias to match class properties
            $query = $con->prepare("SELECT id, nombre AS name FROM categorias WHERE nombre LIKE :nombre");
            $query->execute([":nombre" => "%" . $nameQ . "%"]);

            // Map results to Category objects
            $query->setFetchMode(PDO::FETCH_CLASS, "Category");
            $categories = $query->fetchAll();

            if ($categories) {
                foreach ($categories as $category) {
                    mostrarFila($category);
                }
            } else {
                echo "<p style='color:red'><b>No existe ninguna categoría con esos datos.</b></p>";
            }

            echo "</table>";
        }
    }
?>
</body>
</html>

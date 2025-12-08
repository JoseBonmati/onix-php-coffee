<?php 
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can view this page
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");

        // Validate name
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Check if category already exists
        if (empty($errorMessages)) {
            $check = $con->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre");
            $check->execute([":nombre" => $name]);
            if ($check->fetchColumn() > 0) {
                $errorMessages[] = "La categoría ya está registrada, use otro nombre.";
            }
        }

        // Insert new category if no errors
        if (empty($errorMessages)) {
            $stmt = $con->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
            $stmt->execute([":nombre" => $name]);

            if ($stmt->rowCount() > 0) {
                header("Location: categoriaConsulta.php?nameN=$name");
            } else {
                $errorMessages[] = "Ha ocurrido un error con la base de datos";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevas categorías</title>
    <style> .error{border: solid 2px #FF0000;} </style>
</head>
<body>

    <h1>Formulario de creación de categorías</h1>
    <p>Rellene los siguientes datos:</p>

    <div id="errores" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <form name="fCreacion" id="fCreacion" method="post" action="categoriaCrear.php">

        <fieldset>
            <legend>Datos de la categoría:</legend>
            Nombre: <input type="text" name="nombre" id="nombre" size="50" maxlength="100" value="<?php if(isset($_POST['nombre'])) echo htmlspecialchars($_POST['nombre']); ?>">
        </fieldset>

        <br>
        <a href="categoriaConsulta.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Enviar" name="enviar"/>
    </form>

    <script type="text/javascript" src="categoriasValidacionForm.js"></script>
</body>
</html>

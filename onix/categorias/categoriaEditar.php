<?php 
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    // Get category ID from POST or GET
    if (isset($_POST["enviar"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
    }

    // Handle form submission (update category name)
    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");

        // Validations
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Check for duplicate category name
        if (empty($errorMessages)) {
            $check = $con->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre AND id != :id");
            $check->execute([
                ":nombre" => $name,
                ":id" => $id
            ]);
            if ($check->fetchColumn() > 0) {
                $errorMessages[] = "Ya existe otra categoría con ese nombre.";
            }
        }

        // Base SQL update
        $sql = "UPDATE categorias SET nombre = :nombre WHERE id = :id";
        $params = [
            ":nombre" => $name,
            ":id" => $id
        ];

        // Execute update if no errors
        if (empty($errorMessages)) {
            $updateQuery = $con->prepare($sql);
            $updateQuery->execute($params);

            if ($updateQuery->rowCount() >= 0) {
                header("Location: categoriaConsulta.php?nameE=$name");
                exit;
            } else {
                $errorMessages[] = "No se ha podido actualizar la categoría en la base de datos.";
            }
        }
    }

    // Retrieve category data for form
    $query = $con->prepare("SELECT id, nombre FROM categorias WHERE id = :id");
    $query->execute([":id" => $id]);
    if ($row = $query->fetch()) {
        $nameM = $row["nombre"];
    } else {
        $errorMessages[] = "No se ha encontrado la categoría con el ID proporcionado.";
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar categoría</title>
    <style>.error{border: solid 2px #FF0000;}</style>
</head>
<body>

    <h1>Formulario de edición de categoría</h1>
    <p>Todos los campos son obligatorios:</p>

    <div id="errores" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <?php if (empty($errorMessages) || isset($_POST["enviar"])): ?>
    <form name="fEdicion" id="fEdicion" method="post" action="categoriaEditar.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <fieldset>
            <legend>Datos de la categoría:</legend>        
            Nombre: <input type="text" name="nombre" size="50" maxlength="100" value="<?= htmlspecialchars($nameM) ?>">
            <br><br>
        </fieldset>

        <br>
        <a href="categoriaConsulta.php" 
           style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Guardar cambios" name="enviar">
    </form>
    <?php endif; ?>
    
    <script type="text/javascript" src="categoriasValidacionForm.js"></script>
</body>
</html>

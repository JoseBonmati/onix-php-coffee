<?php
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can delete users
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    $name = "";
    $email = "";
    $id = null;

    // Retrieve user data by ID
    if (isset($_GET["id"])) {
        $id = (int) $_GET["id"];

        $query = $con->prepare("SELECT nombre, email FROM usuarios WHERE id = :id");
        $query->execute([":id" => $id]);

        if ($data = $query->fetch()) {
            $name = $data["nombre"];
            $email = $data["email"];
        } else {
            $errorMessages[] = "No se ha encontrado el usuario con el ID proporcionado.";
        }
    }

    // Handle deletion request
    if (isset($_POST["eliminar"])) {
        $id = (int) $_POST["id"];

        if ($_SESSION["id"] == $id) {
            $errorMessages[] = "No puede eliminar su propio usuario.";
        } else {
            $checkQuery = $con->prepare("SELECT nombre FROM usuarios WHERE id = :id");
            $checkQuery->execute([":id" => $id]);

            if ($data = $checkQuery->fetch()) {
                $name = $data["nombre"];

                $deleteQuery = $con->prepare("DELETE FROM usuarios WHERE id = :id");
                $deleteQuery->execute([":id" => $id]);

                if ($deleteQuery->rowCount() > 0) {
                    header("Location: usuarioConsulta.php?nameD=$name");
                    exit;
                } else {
                    $errorMessages[] = "No se ha podido eliminar el usuario.";
                }
            } else {
                $errorMessages[] = "El usuario indicado no existe.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrar usuarios</title>
</head>
<body>
    <h1>Confirmación de borrado de usuarios</h1>

    <div id="errores" style="color:red">
        <?php
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <?php if (empty($errorMessages) && $id !== null): ?>
        <p>¿Desea eliminar al usuario <?= htmlspecialchars($name) ?> (<?= htmlspecialchars($email) ?>)?</p>

        <form name="fBorrado" id="fBorrado" method="post" action="usuarioEliminar.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

            <a href="usuarioConsulta.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
            <input type="submit" value="Eliminar usuario" name="eliminar">
        </form>
    <?php endif; ?>
</body>
</html>

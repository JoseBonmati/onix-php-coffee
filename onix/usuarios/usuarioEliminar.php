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
                    header("Location: usuarioConsulta.php?nameD=" . urlencode($name));
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
    <title>Eliminar usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg">
        <div class="p-4 onix-card" style="max-width: 600px;">
            <h2 class="text-center mb-4 fw-bold">Eliminar usuario</h2>

            <!-- Error messages -->
            <div class="mb-3">
                <?php
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <?php if (empty($errorMessages) && $id !== null): ?>
                <p class="text-center fw-semibold mb-4">
                    ¿Desea eliminar al usuario<br>
                    <span class="text-onix fw-bold"><?= htmlspecialchars($name) ?></span><br>
                    (<?= htmlspecialchars($email) ?>)?
                </p>
                <form name="fBorrado" id="fBorrado" method="post" action="usuarioEliminar.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                    <div class="d-grid gap-3">
                        <button type="submit" name="eliminar" class="btn btn-danger fw-semibold" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar usuario</button>
                        <hr class="onix-divider">
                        <a href="usuarioConsulta.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

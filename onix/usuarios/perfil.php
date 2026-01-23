<?php

    require_once "../utilidades/conectar_db.php";
    session_start();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    $con = conectar();
    $sessionId = $_SESSION["id"];

    // Retrieve user data
    $query = $con->prepare("SELECT id, nombre, email, telefono, rol FROM usuarios WHERE id = :id");
    $query->execute([":id" => $sessionId]);
    $user = $query->fetch();

    if (!$user) {
        echo "<p style='color:red'><b>No se han podido cargar los datos del usuario.</b></p>";
        exit;
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Onix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <main class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="p-4 onix-card" style="max-width: 600px; width: 100%;">
            <h2 class="text-center mb-4 fw-bold">Mi Perfil</h2>

            <!-- Success message after editing profile -->
            <?php
                if (isset($_GET["nameE"]) && isset($_GET["emailE"])) {
                    $nameE = htmlspecialchars($_GET["nameE"]);
                    $emailE = htmlspecialchars($_GET["emailE"]);
                    echo "<p class='alert alert-success text-center mb-4'>El usuario <b>$nameE</b> con email <b>$emailE</b> ha sido modificado correctamente.</p>";
                }
            ?>

            <div class="text-center fs-5 mb-4">
                <p class="mb-2"><strong><?= htmlspecialchars($user["nombre"]) ?></strong></p>
                <p class="mb-2"><strong><?= htmlspecialchars($user["email"]) ?></strong></p>
                <p class="mb-2"><strong><?= htmlspecialchars($user["telefono"]) ?></strong></p>
            </div>

            <div class="d-grid gap-3 mt-4">
                <?php if ($_SESSION["rol"] === "administrador"): ?>
                    <a href="usuarioEditar.php?onlyMine=1&id=<?= $_SESSION["id"] ?>" class="btn btn-onix fw-semibold">Editar mis datos</a>
                <?php else: ?>
                    <a href="usuarioEditar.php?id=<?= $_SESSION["id"] ?>" class="btn btn-onix fw-semibold">Editar mis datos</a>
                <?php endif; ?>

                <?php if ($_SESSION["rol"] === "administrador"): ?>
                    <a href="../reservas/reservaConsulta.php?onlyMine=1" class="btn btn-outline-onix fw-semibold">Mis reservas</a>
                <?php else: ?>
                    <a href="../reservas/reservaConsulta.php" class="btn btn-outline-onix fw-semibold">Mis reservas</a>
                <?php endif; ?>

                <hr class="onix-divider">

                <a href="cerrarSesion.php" class="btn btn-outline-danger fw-semibold">Cerrar sesión</a>

                <?php if ($user["rol"] !== "administrador"): ?>
                    <a href="usuarioDesactivar.php?id=<?= htmlspecialchars(urlencode($user["id"])) ?>&action=deactivate" class="btn btn-outline-warning fw-semibold"
                    onclick="return confirm('¿Seguro que deseas desactivar tu cuenta?');">Desactivar mi cuenta</a>
                <?php endif; ?>
                
                <a href="../index.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $db = Database::getConnection();
    $sessionId = $_SESSION["id"];

    // Retrieve user data using SQL aliases
    $query = $db->prepare("SELECT id, nombre AS name, email, telefono AS phone, rol AS role FROM usuarios WHERE id = :id");
    $query->execute([":id" => $sessionId]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<p style='color:red'><b>No se han podido cargar los datos del usuario.</b></p>";
        exit;
    }

    // Assign mapped role check for cleaner conditionals below
    $isAdmin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Onix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <main class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="p-4 onix-card" style="max-width: 600px; width: 100%;">
            <h2 class="text-center mb-4 fw-bold">Mi Perfil</h2>

            <!-- Success message after editing profile -->
            <?php
                if (isset($_GET["edited_name"]) && isset($_GET["edited_email"])) {
                    $editedName = htmlspecialchars($_GET["edited_name"]);
                    $editedEmail = htmlspecialchars($_GET["edited_email"]);
                    echo "<p class='alert alert-success text-center mb-4'>El usuario <b>$editedName</b> con email <b>$editedEmail</b> ha sido modificado correctamente.</p>";
                }
            ?>

            <div class="text-center fs-5 mb-4">
                <p class="mb-2"><strong><?= htmlspecialchars($user["name"]) ?></strong></p>
                <p class="mb-2"><strong><?= htmlspecialchars($user["email"]) ?></strong></p>
                <p class="mb-2"><strong><?= htmlspecialchars($user["phone"]) ?></strong></p>
            </div>

            <div class="d-grid gap-3 mt-4">
                <?php if ($isAdmin): ?>
                    <a href="/users/user_edit.php?only_mine=1&id=<?= $_SESSION["id"] ?>" class="btn btn-onix fw-semibold">Editar mis datos</a>
                <?php else: ?>
                    <a href="/users/user_edit.php?id=<?= $_SESSION["id"] ?>" class="btn btn-onix fw-semibold">Editar mis datos</a>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <a href="/bookings/booking_list.php?only_mine=1" class="btn btn-outline-onix fw-semibold">Mis reservas</a>
                <?php else: ?>
                    <a href="/bookings/booking_list.php" class="btn btn-outline-onix fw-semibold">Mis reservas</a>
                <?php endif; ?>

                <hr class="onix-divider">

                <a href="/users/logout.php" class="btn btn-outline-danger fw-semibold">Cerrar sesión</a>

                <!-- Check against raw DB value -->
                <?php if ($user["role"] !== "administrador"): ?>
                    <a href="/users/user_deactivate.php?id=<?= htmlspecialchars(urlencode($user["id"])) ?>&action=deactivate&only_mine=1" class="btn btn-outline-warning fw-semibold"
                    onclick="return confirm('¿Seguro que deseas desactivar tu cuenta?');">Desactivar mi cuenta</a>
                <?php endif; ?>
                
                <a href="/index.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php

    session_start();
    require_once "Database.php";

    $db = Database::getConnection();

    // Restrict access: only administrators can view this panel
    if (!isset($_SESSION["id"]) || $_SESSION["role"] !== "admin") {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $sessionId = $_SESSION["id"];

    // Retrieve administrator name for greeting
    $adminStmt = $db->prepare("SELECT nombre AS name FROM usuarios WHERE id = :id");
    $adminStmt->execute([":id" => $sessionId]);
    $adminUser = $adminStmt->fetch(PDO::FETCH_ASSOC);
    
    $adminName = $adminUser ? $adminUser["name"] : "Administrador";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
    <title>Panel Administración Onix</title>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center py-5 onix-bg ">
        <div class="p-4 text-center onix-card">
            <h2 class="fw-bold mb-4">Panel de Administración</h2>
            <p class="fw-semibold mb-4">Bienvenido/a, <?= htmlspecialchars($adminName) ?></p>
            <div class="d-grid gap-3">
                <a href="/users/user_list.php" class="btn fw-semibold btn-onix">Gestión de Usuarios</a>
                <a href="/products/product_list.php" class="btn fw-semibold btn-onix">Gestión de Productos</a>
                <a href="/bookings/booking_list.php" class="btn fw-semibold btn-onix">Gestión de Reservas</a>
                <a href="/categories/category_list.php" class="btn fw-semibold btn-onix">Gestión de Categorías</a>
                <hr class="onix-divider">
                <a href="/index.php" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
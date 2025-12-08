<?php
    require_once "conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can view this panel
    if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    $sessionId = $_SESSION["id"];

    // Retrieve administrator name for greeting
    $query = $con->prepare("SELECT nombre FROM usuarios WHERE id = :id");
    $query->execute([":id" => $sessionId]);
    $admin = $query->fetch();
    $adminName = $admin ? $admin["nombre"] : "Administrador";
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <header class="sticky-top bg-dark border-3 border-bottom border-info py-2">
        <nav class="navbar navbar-dark navbar-expand-lg container-fluid">
            <a class="navbar-brand ms-3" href="../index.php">
                <img src="../assets/logos/taza-cafe.png" alt="Logo" width="64" height="64">
            </a>
            <button class="navbar-toggler me-3 border-info" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin" aria-controls="navAdmin" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navAdmin" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center fs-4 fw-semibold gap-lg-4">
                    <li class="nav-item"><a class="nav-link text-light" href="../carta.php">Carta</a></li>
                    <li class="nav-item"><a class="nav-link text-light" href="../contacto.php">Contacto</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container py-5 text-center">
        <h1 class="mb-4">Bienvenido/a <?= htmlspecialchars($adminName) ?></h1>
        <div class="d-flex flex-column gap-3 mx-auto" style="max-width: 400px;">
            <a href="../usuarios/usuarioConsulta.php" class="btn btn-info btn-lg">Gestión de Usuarios</a>
            <a href="../productos/productoConsulta.php" class="btn btn-info btn-lg">Gestión de Productos</a>
            <a href="../reservas/reservaConsulta.php" class="btn btn-outline-info btn-lg">Gestión de Reservas</a>
            <a href="../categorias/categoriaConsulta.php" class="btn btn-info btn-lg">Gestión de Categorías</a>
        </div>
    </main>
</body>
</html>

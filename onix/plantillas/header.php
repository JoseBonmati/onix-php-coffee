<?php

    require_once "utilidades/conectar_db.php";
    $con = conectar();

    session_start();

?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cafetería Onix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg container-fluid">
            <a class="navbar-brand ms-3" href="index.php">
                <img src="../assets/logos/logo-pequenyo.png" alt="Logo" width="auto" height="auto" style="max-height: 60px;">
            </a>
            <button class="navbar-toggler me-3" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal" aria-controls="navPrincipal" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="navPrincipal" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center fs-4 fw-semibold gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carta.php">Carta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacto.php">Contacto</a>
                    </li>
                    <li class="nav-item me-lg-3">
                        <?php if (isset($_SESSION["id"])): ?>
                            <a href="../usuarios/perfil.php" class="nav-link">
                                <img src="../assets/logos/perfil.png" alt="Perfil" class="rounded-circle" width="48" height="48">
                            </a>
                        <?php else: ?>
                            <a href="../usuarios/login.php" class="nav-link">
                                <img src="../assets/logos/perfil.png" alt="Perfil" class="rounded-circle" width="48" height="48">
                            </a>
                        <?php endif; ?>
                    </li>
                    <?php if (isset($_SESSION["rol"]) && $_SESSION["rol"] === "administrador"): ?>
                        <li class="nav-item">
                            <a class="btn btn-onix fw-semibold me-lg-4 my-2 my-lg-0" href="../utilidades/panelAdministrador.php">Panel de Administración</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
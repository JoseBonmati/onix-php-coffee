<?php
    session_start();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carta Onix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-dark">
    <header class="sticky-top bg-dark border-3 border-bottom border-info py-2">
        <nav class="navbar navbar-dark navbar-expand-lg container-fluid">
            <a class="navbar-brand ms-3" href="index.php">
                <img src="../assets/logos/taza-cafe.png" alt="Logo" width="64" height="64">
            </a>
            <button class="navbar-toggler me-3 border-info" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal" aria-controls="navPrincipal" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="navPrincipal" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center fs-4 fw-semibold gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="contacto.php">Contacto</a>
                    </li>
                    <li class="nav-item me-lg-3">
                        <?php if (isset($_SESSION["id"])): ?>
                            <a href="../usuarios/perfil.php" class="nav-link">
                                <img src="../assets/logos/perfil.png" alt="Perfil de usuario" class="rounded-circle border border-3 border-info" width="48" height="48">
                            </a>
                        <?php else: ?>
                            <a href="../usuarios/login.php" class="nav-link">
                                <img src="../assets/logos/perfil.png" alt="Acceder" class="rounded-circle border border-3 border-info" width="48" height="48">
                            </a>
                        <?php endif; ?>
                    </li>
                    <?php if (isset($_SESSION["rol"]) && $_SESSION["rol"] === "administrador"): ?>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../utilidades/panelAdministrador.php">Panel de Administración</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <section class="container py-5">
        <h1 class="text-center mb-5 display-5 fw-bold text-light">Carta Cafetería Onix</h1>
        <div class="row align-items-start">
            <div class="col-lg-6 text-center text-lg-start">
                <h2 class="text-light fw-bold mb-4">Bocadillos</h2>
                <ul class="list-group list-group-flush fs-4">
                    <li class="list-group-item bg-dark text-light border-0 px-0">
                        <strong>Bocadillo 1</strong> – 4,50€<br>
                        Descripción bocadillo 1.<br>
                    </li>
                    <li class="list-group-item bg-dark text-light border-0 px-0">
                        <strong>Bocadillo 2</strong> – 4,90€<br>
                        Descripción bocadillo 2.<br>
                    </li>
                    <li class="list-group-item bg-dark text-light border-0 px-0">
                        <strong>Bocadillo 3</strong> – 4,90€<br>
                        Descripción bocadillo 3.
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div id="carouselPlatosEspeciales" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded shadow">
                        <div class="carousel-item active">
                            <img src="../assets/imagenes/bocadillo1.jpg" class="d-block w-100" alt="Bocadillo 1">
                            <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                <h5 class="text-light mb-0">Bocadillo 1</h5>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="../assets/imagenes/bocadillo2.jpg" class="d-block w-100" alt="Bocadillo 2">
                            <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                <h5 class="text-light mb-0">Bocadillo 2</h5>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="../assets/imagenes/bocadillo3.jpg" class="d-block w-100" alt="Bocadillo 3">
                            <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                <h5 class="text-light mb-0">Bocadillo 3</h5>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn" type="button" data-bs-target="#carouselPlatosEspeciales" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="btn" type="button" data-bs-target="#carouselPlatosEspeciales" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="container py-5">
        <div class="row align-items-start">
            <div class="col-lg-6">
                <div id="carouselHamburguesas" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded shadow">
                        <div class="carousel-item active">
                            <img src="../assets/imagenes/hamburguesa1.jpg" class="d-block w-100" alt="Hamburguesa 1">
                            <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                <h5 class="text-light mb-0">Hamburguesa 1</h5>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="../assets/imagenes/hamburguesa2.jpg" class="d-block w-100" alt="Hamburguesa 2">
                            <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                <h5 class="text-light mb-0">Hamburguesa 2</h5>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="../assets/imagenes/hamburguesa3.jpg" class="d-block w-100" alt="Hamburguesa 3">
                            <div class="carousel-caption d-block bg-dark bg-opacity-75 rounded py-2">
                                <h5 class="text-light mb-0">Hamburguesa 3</h5>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn" type="button" data-bs-target="#carouselHamburguesas" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="btn" type="button" data-bs-target="#carouselHamburguesas" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-end">
                <h2 class="text-light fw-bold mb-4">Hamburguesas</h2>
                <ul class="list-group list-group-flush fs-4">
                    <li class="list-group-item bg-dark text-light border-0 px-0">
                        <strong>Hamburguesa 1</strong> – 6,50€<br>
                        Descripción hamburguesa 1.
                    </li>
                    <li class="list-group-item bg-dark text-light border-0 px-0">
                        <strong>Hamburguesa 2</strong> – 6,00€<br>
                        Descripción hamburguesa 2.
                    </li>
                    <li class="list-group-item bg-dark text-light border-0 px-0">
                        <strong>Hamburguesa 3</strong> – 6,20€<br>
                        Descripción hamburguesa 3.
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <hr class="opacity-100 w-75 mx-auto border border-2 border-info">
    <section class="container py-5">
        <h2 class="text-center text-light fw-bold mb-5 display-6">Café e infusiones</h2>
        <div class="row row-cols-1 row-cols-lg-2 gx-5 gy-4 fs-5">
            <div class="col">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Café Solo</span><span>1,40€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Café Americano</span><span>1,80€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Bombón</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Carajillo</span><span>2,10€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Té Negro</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Té Verde</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Rooibos de Vainilla</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Poleo</span><span>2,30€</span>
                    </li>
                </ul>
            </div>
            <div class="col">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Café Cortado</span><span>1,60€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Café con Leche</span><span>1,90€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Capuccino</span><span>2,40€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Té Chai</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Té Rojo</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Té Matcha</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Pacífico</span><span>2,30€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Manzanilla</span><span>2,30€</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <hr class="opacity-100 w-75 mx-auto border border-2 border-info">
    <section class="container py-5">
        <h2 class="text-center text-light fw-bold mb-5 display-6">Bebidas</h2>
        <div class="row row-cols-1 row-cols-lg-2 gx-5 gy-4 fs-5">
            <div class="col">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Agua mineral</span><span>1,20€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Coca-Cola</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Coca-Cola Zero</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Fanta Naranja</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Fanta Limón</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Sprite</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Nestea</span><span>2,20€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Zumo de naranja</span><span>2,50€</span>
                    </li>
                </ul>
            </div>
            <div class="col">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Zumo de piña</span><span>2,50€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Zumo multifrutas</span><span>2,50€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Cerveza</span><span>2,20€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Cerveza sin alcohol</span><span>2,20€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Tónica</span><span>2,00€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Agua con gas</span><span>1,50€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Batido de chocolate</span><span>2,80€</span>
                    </li>
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between px-0 border-0">
                        <span>Batido de vainilla</span><span>2,80€</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <footer class="bg-dark text-light border-top border-3 border-info pt-5">
        <div class="container border-bottom border-3 border-info pb-3">
            <div class="row text-center text-lg-start gy-4 align-items-center fs-5">
                <div class="col-lg-4">
                    <h6 class="text-uppercase fw-bold mb-3 fs-5">Correo</h6>
                    <p class="mb-1">correo@cafeteriaonix.com</p>
                    <h6 class="text-uppercase fw-bold mt-4 mb-3 fs-5">Dirección</h6>
                    <address class="mb-0">
                        Carrer Poeta Miguel Hernandez, 36<br>
                        03201 Elche, Alicante
                    </address>
                </div>
                <div class="col-lg-4 text-center">
                    <img src="../assets/logos/logo-grande.png" alt="Logo grande" class="img-fluid" style="max-width: 300px;">
                </div>
                <div class="col-lg-4 text-lg-end text-center">
                    <h6 class="text-uppercase fw-bold mb-3 fs-5">Teléfono</h6>
                    <p class="mb-1">123 456 789</p>
                    <div class="mt-4 d-flex gap-3 justify-content-lg-end justify-content-center">
                        <a href="https://www.instagram.com" target="_blank" class="text-light fs-4">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.facebook.com" target="_blank" class="text-light fs-4">
                            <i class="bi bi-facebook"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-light text-center py-3 mt-1">
            <div class="container small">
                <div class="row justify-content-between align-items-center">
                    <div class="col-lg-auto mb-2 mb-lg-0">
                        © 2025 Cafeteria Onix | Diseñado y desarrollado por Jose
                    </div>
                    <div class="col-lg-auto">
                        <a href="#legal" class="link-light me-3">Aviso legal</a>
                        <a href="#privacidad" class="link-light me-3">Política de privacidad</a>
                        <a href="#cookies" class="link-light">Política de cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
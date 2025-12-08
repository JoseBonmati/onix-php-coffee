<?php
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
                        <a class="nav-link text-light" href="carta.php">Carta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="contacto.php">Contacto</a>
                    </li>
                    <li class="nav-item me-lg-3">
                        <?php if (isset($_SESSION["id"])): ?>
                            <a href="../usuarios/perfil.php" class="nav-link">
                                <img src="../assets/logos/perfil.png" alt="Perfil" class="rounded-circle border border-3 border-info" width="48" height="48">
                            </a>
                        <?php else: ?>
                            <a href="../usuarios/login.php" class="nav-link">
                                <img src="../assets/logos/perfil.png" alt="Perfil" class="rounded-circle border border-3 border-info" width="48" height="48">
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
    <section class="py-5 text-center bg-dark">
        <div class="container">
            <img src="../assets/logos/logo-grande.png" alt="Logo grande" class="img-fluid mb-4" style="max-width: 350px;">
            <div class="d-flex justify-content-around">
                <a href="contacto.php" class="btn btn-info btn-lg px-5 py-3">Reservar</a>
                <a href="carta.php" class="btn btn-outline-info btn-lg px-5 py-3">Ver carta</a>
            </div>
        </div>
    </section>
    <hr class="opacity-100 w-75 mx-auto border border-2 border-info">
    <section class="py-5 bg-dark">
        <div class="container text-center text-light">
            <h1 class="my-3">Cafeteria en Elche</h1>
            <p class="fs-5">
                Cafetería Onix es tu rincón ideal para disfrutar de un buen café, desayunos caseros y un ambiente acogedor. 
                Un espacio pensado para relajarte, trabajar o compartir momentos especiales, con productos de calidad y atención cercana. ¡Ven y descubre lo que hace único a Onix!
            </p>
        </div>
        <div class="container-fluid p-0 my-5">
            <div class="row g-0 justify-content-center">
                <div class="col-12 col-lg-9">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <img src="../assets/imagenes/cafeteria-local.jpg" class="img-fluid w-100" alt="Imagen 1">
                        </div>
                        <div class="col-lg-6">
                            <img src="../assets/imagenes/cafeteria-local.jpg" class="img-fluid w-100" alt="Imagen 2">
                        </div>
                        <div class="col-lg-6">
                            <img src="../assets/imagenes/cafeteria-local.jpg" class="img-fluid w-100" alt="Imagen 3">
                        </div>
                        <div class="col-lg-6">
                            <img src="../assets/imagenes/cafeteria-local.jpg" class="img-fluid w-100" alt="Imagen 4">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <hr class="opacity-100 w-75 mx-auto border border-2 border-info">
    <section class="py-5 bg-dark my-5">
        <div class="container my-5">
            <h2 class="h2 fw-semibold mb-5 text-center text-light">Reseñas de clientes</h2>
            <div class="row g-4 fs-5">
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="card-text">“Café excelente y personal amable. El desayuno del domingo fue top.”</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning">★★★★★</span>
                                <small class="text-muted">Alicia • Google</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="card-text">“Local tranquilo para trabajar. Repetiré.”</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning">★★★★☆</span>
                                <small class="text-muted">Jose • Maps</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <p class="card-text">“Los postres caseros están de vicio. Recomendado.”</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning">★★★★★</span>
                                <small class="text-muted">Juan • Trip</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <hr class="opacity-100 w-75 mx-auto border border-2 border-info">
    <section class="py-5 my-5">
        <div class="container">
            <div class="row g-4 fs-5">
                <div class="col-lg-5">
                    <h2 class="h2 fw-semibold mb-3 text-center text-light">Horario</h2>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Lunes - Viernes</span><span class="fw-semibold">08:00–20:00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Sábado</span><span class="fw-semibold">09:00–22:00</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Domingo</span><span class="fw-semibold">09:00–15:00</span>
                        </li>
                    </ul>
                    <div class="d-flex justify-content-center">
                        <a href="contacto.php" class="btn btn-info mt-3 w-100 py-2 fs-5">Reservar mesa</a>
                    </div>
                </div>
                <div class="col-lg-7">
                    <h2 class="h3 fw-semibold mb-3">Mapa</h2>
                    <div class="ratio ratio-16x9 rounded overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6265.294229335919!2d-0.7074452235700485!3d38.26448968379929!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd63b701541d1b2f%3A0xa9734591f8df6b3f!2sCafeteria%20Onix!5e0!3m2!1ses!2ses!4v1764614579572!5m2!1ses!2ses" 
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
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
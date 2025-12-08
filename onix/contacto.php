<?php
    session_start();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva mesa en Onix</title>
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
                        <a class="nav-link text-light" href="carta.php">Carta</a>
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
        <h1 class="text-center text-light fw-bold mb-5 display-5">Contacto</h1>
        <hr class="opacity-100 w-25 mx-auto border border-2 border-info">
        <div class="row justify-content-center text-light fs-4 mt-5">
            <div class="col-md-6 mb-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-geo-alt-fill display-4 text-info"></i>
                </div>
                <h2 class="fw-bold text-info my-4">Dirección</h2>
                <p class="mb-0">
                    Carrer Poeta Miguel Hernandez, 36, 03201 Elche, Alicante
                </p>
            </div>
            <div class="col-md-6 mb-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-telephone-fill display-4 text-info"></i>
                </div>
                <h2 class="fw-bold text-info my-4">Teléfono</h2>
                <p class="mb-0">123 456 789</p>
            </div>
        </div>
    </section>
    <hr class="opacity-100 w-75 mx-auto border border-2 border-info">
    <section class="container py-5">
        <div class="row gx-5 gy-4 align-items-start">
            <div class="col-lg-6">
                <div class="bg-dark text-light p-4">
                    <h2 class="text-info fw-bold mb-4 text-center">Diciembre 2025</h2>
                    <div class="d-grid gap-2 text-center">
                        <div class="d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 0.5rem;"> <!-- Definitivamente no es recomendable hacer un calendario asi -->
                            <div class="text-center fw-bold text-info">L</div>
                            <div class="text-center fw-bold text-info">M</div>
                            <div class="text-center fw-bold text-info">Mi</div>
                            <div class="text-center fw-bold text-info">J</div>
                            <div class="text-center fw-bold text-info">V</div>
                            <div class="text-center fw-bold text-info">S</div>
                            <div class="text-center fw-bold text-info">D</div>
                            <button class="btn btn-outline-info">1</button>
                            <button class="btn btn-info fw-bold">2</button>
                            <button class="btn btn-outline-info">3</button>
                            <button class="btn btn-outline-info">4</button>
                            <button class="btn btn-outline-info">5</button>
                            <button class="btn btn-outline-info">6</button>
                            <button class="btn btn-outline-info">7</button>
                            <button class="btn btn-outline-info">8</button>
                            <button class="btn btn-outline-info">9</button>
                            <button class="btn btn-outline-info">10</button>
                            <button class="btn btn-outline-info">11</button>
                            <button class="btn btn-outline-info">12</button>
                            <button class="btn btn-outline-info">13</button>
                            <button class="btn btn-outline-info">14</button>
                            <button class="btn btn-outline-info">15</button>
                            <button class="btn btn-outline-info">16</button>
                            <button class="btn btn-outline-info">17</button>
                            <button class="btn btn-outline-info">18</button>
                            <button class="btn btn-outline-info">19</button>
                            <button class="btn btn-outline-info">20</button>
                            <button class="btn btn-outline-info">21</button>
                            <button class="btn btn-outline-info">22</button>
                            <button class="btn btn-outline-info">23</button>
                            <button class="btn btn-outline-info">24</button>
                            <button class="btn btn-outline-info">25</button>
                            <button class="btn btn-outline-info">26</button>
                            <button class="btn btn-outline-info">27</button>
                            <button class="btn btn-outline-info">28</button>
                            <button class="btn btn-outline-info">29</button>
                            <button class="btn btn-outline-info">30</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-dark text-light p-4">
                    <h2 class="text-info fw-bold mb-4 text-center">Reserva</h2>
                    <form>
                        <div class="mb-3">
                            <label for="personas" class="form-label">Personas</label>
                            <select id="personas" class="form-select">
                                <option selected disabled>Seleccione</option>
                                <option>2 personas</option>
                                <option>3 personas</option>
                                <option>4 personas</option>
                                <option>5 personas</option>
                                <option>6 personas</option>
                                <option>7 personas</option>
                                <option>8 personas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="zona" class="form-label">Zona</label>
                            <select id="zona" class="form-select">
                                <option selected disabled>Seleccione la zona</option>
                                <option>Terraza</option>
                                <option>Interior</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="hora" class="form-label">Hora</label>
                            <select id="hora" class="form-select">
                                <option selected disabled>Seleccione</option>
                                <option>12:00</option>
                                <option>12:30</option>
                                <option>13:00</option>
                                <option>13:30</option>
                                <option>14:00</option>
                                <option>14:30</option>
                                <option>15:00</option>
                                <option>15:30</option>
                                <option>16:00</option>
                                <option>16:30</option>
                                <option>17:00</option>
                                <option>17:30</option>
                                <option>18:00</option>
                                <option>18:30</option>
                                <option>19:00</option>
                                <option>19:30</option>
                                <option>20:00</option>
                            </select>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-info px-5 fw-bold">Reservar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <section class="container py-5 mb-5">
        <div class="ratio ratio-16x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6265.294229335919!2d-0.7074452235700485!3d38.26448968379929!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd63b701541d1b2f%3A0xa9734591f8df6b3f!2sCafeteria%20Onix!5e0!3m2!1ses!2ses!4v1764693711563!5m2!1ses!2ses" 
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
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
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfil de usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <header class="sticky-top bg-dark border-3 border-bottom border-info py-2">
        <nav class="navbar navbar-dark navbar-expand-lg container-fluid">
            <a class="navbar-brand ms-3" href="../index.php">
                <img src="../assets/logos/taza-cafe.png" alt="Logo" width="64" height="64">
            </a>
            <button class="navbar-toggler me-3 border-info" type="button" data-bs-toggle="collapse" data-bs-target="#navPerfil" aria-controls="navPerfil" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div id="navPerfil" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center fs-4 fw-semibold gap-lg-4">
                    <li class="nav-item"><a class="nav-link text-light" href="../carta.php">Carta</a></li>
                    <li class="nav-item"><a class="nav-link text-light" href="../contacto.php">Contacto</a></li>
                    <?php if ($_SESSION["rol"] === "administrador"): ?>
                        <li class="nav-item"><a class="nav-link text-light" href="usuarioConsulta.php">Panel de Administración</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container py-5">
        <h1 class="text-center mb-4">Mi Perfil</h1>

        <?php
            // Success message after editing profile
            if (isset($_GET["nameE"]) && isset($_GET["emailE"])) {
                $nameE = htmlspecialchars($_GET["nameE"]);
                $emailE = htmlspecialchars($_GET["emailE"]);
                echo "<p class='text-success text-center'><b>El usuario $nameE con email $emailE ha sido modificado correctamente.</b></p>";
            }
        ?>

        <div class="card bg-dark border-info shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-body fs-5 text-light text-center">
                <p><strong><?= htmlspecialchars($user["nombre"]) ?></strong></p>
                <p><strong><?= htmlspecialchars($user["email"]) ?></strong></p>
                <p><strong><?= htmlspecialchars($user["telefono"]) ?></strong></p>

                <div class="d-flex justify-content-between mt-4">
                    <a href="usuarioEditar.php?id=<?= htmlspecialchars(urlencode($user["id"])) ?>" class="btn btn-info px-4">Editar perfil</a>
                    <a href="cerrarSesion.php" class="btn btn-outline-danger px-4">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php
    require_once "../utilidades/conectar_db.php";
    session_start();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: ../usuarios/login.php?acceso=denegado");
        exit;
    }

    $con = conectar();
    $userId = $_SESSION["id"];

    // Check that a reserve ID is received
    if (!isset($_GET["id"])) {
        header("Location: ../contacto.php");
        exit;
    }

    $reserveId = (int) $_GET["id"];
    
    $sql = $con->prepare("SELECT id, fecha, hora, num_personas FROM reservas WHERE id = :id AND id_usuario = :idUsuario");

    $sql->execute([
        ":id" => $reserveId,
        ":idUsuario" => $userId
    ]);

    $reserve = $sql->fetch();

    if (!$reserve) {
        header("Location: ../contacto.php?error=reservaNoEncontrada");
        exit;
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva confirmada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="onix-card-wide p-5 text-center text-light">

                    <h2 class="fw-bold text-onix mb-3">¡Reserva confirmada!</h2>
                    <p class="mb-4">Tu reserva se ha registrado correctamente.</p>

                    <hr class="onix-divider my-4">

                    <h4 class="fw-bold text-onix mb-3">Detalles de la reserva</h4>

                    <p class="mb-1"><strong>ID de reserva:</strong> <?= $reserve["id"] ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?= date("d/m/Y", strtotime($reserve["fecha"])) ?></p>
                    <p class="mb-1"><strong>Hora:</strong> <?= htmlspecialchars($reserve["hora"]) ?></p>
                    <p class="mb-3"><strong>Personas:</strong> <?= (int)$reserve["num_personas"] ?></p>

                    <hr class="onix-divider my-4">

                    <div class="d-grid gap-3 mt-4">
                        <a href="../index.php" class="btn btn-outline-onix fw-semibold">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

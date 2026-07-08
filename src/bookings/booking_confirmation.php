<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $userId = (int)$_SESSION["id"];

    // Check that a booking ID is received
    if (!isset($_GET["id"])) {
        header("Location: /contact.php");
        exit;
    }

    $bookingId = (int) $_GET["id"];
    
    // Fetch booking details using aliases for consistency
    $sql = $db->prepare("SELECT id, fecha AS date, hora AS time, num_personas AS people FROM reservas WHERE id = :id AND id_usuario = :user_id");

    $sql->execute([
        ":id" => $bookingId,
        ":user_id" => $userId
    ]);

    $booking = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header("Location: /contact.php?error=booking_not_found");
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
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
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

                    <p class="mb-1"><strong>ID de reserva:</strong> <?= htmlspecialchars((string)$booking["id"]) ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?= date("d/m/Y", strtotime($booking["date"])) ?></p>
                    <p class="mb-1"><strong>Hora:</strong> <?= htmlspecialchars((string)$booking["time"]) ?></p>
                    <p class="mb-3"><strong>Personas:</strong> <?= htmlspecialchars((string)$booking["people"]) ?></p>

                    <hr class="onix-divider my-4">

                    <div class="d-grid gap-3 mt-4">
                        <a href="/index.php" class="btn btn-outline-onix fw-semibold">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
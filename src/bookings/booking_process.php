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

    // Collect data from the form
    $date = $_POST["fecha"] ?? "";
    $time = $_POST["hora"] ?? "";
    $people = isset($_POST["personas"]) ? (int)$_POST["personas"] : 0;

    // Basic validation
    if (empty($date) || empty($time) || $people < 1) {
        header("Location: ../contacto.php?error=datosInvalidos");
        exit;
    }

    // Validate maximum number of people allowed
    if ($people > 30) {
        header("Location: ../contacto.php?error=datosInvalidos");
        exit;
    }

    // Validate date format YYYY-MM-DD
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        header("Location: ../contacto.php?error=fechaInvalida");
        exit;
    }

    // Validate that the date exists
    $datePieces = explode("-", $date);
    if (!checkdate($datePieces[1], $datePieces[2], $datePieces[0])) {
        header("Location: ../contacto.php?error=fechaInvalida");
        exit;
    }

    // Validate that it is not past the date
    $today = new DateTime("today");
    $reservationDate = new DateTime($date);

    if ($reservationDate < $today) {
        header("Location: ../contacto.php?error=fechaPasada");
        exit;
    }

    // If reservation is for today, ensure the selected hour is not in the past
    if ($reservationDate == $today) {
        $currentHour = (new DateTime())->format("H:i");

        if ($time <= $currentHour) {
            header("Location: ../contacto.php?error=horaInvalida");
            exit;
        }
    }

    // Validate that it is not Sunday
    if ($reservationDate->format("w") == 0) { // 0 = Sunday
        header("Location: ../contacto.php?error=domingo");
        exit;
    }

    // Validate allowed hours
    $allowedHours = [
        "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
        "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
    ];

    if (!in_array($time, $allowedHours)) {
        header("Location: ../contacto.php?error=horaInvalida");
        exit;
    }

    // Validate maximum capacity (50 people per hour)
    $maximumCapacity = 50;

    $query = $con->prepare("SELECT SUM(num_personas) AS total FROM reservas WHERE fecha = :fecha AND hora = :hora");

    $query->execute([
        ":fecha" => $date,
        ":hora" => $time
    ]);

    $totalCurrent = (int)$query->fetch()["total"];
    $totalAfterReservation = $totalCurrent + $people;

    if ($totalAfterReservation > $maximumCapacity) {
        header("Location: ../contacto.php?error=aforoCompleto");
        exit;
    }

    // Insert reserve
    $insert = $con->prepare("INSERT INTO reservas (id_usuario, fecha, hora, num_personas)
                             VALUES (:id, :fecha, :hora, :personas)");

    $insert->execute([
        ":id" => $userId,
        ":fecha" => $date,
        ":hora" => $time,
        ":personas" => $people
    ]);

    $reserveId = $con->lastInsertId(); 
    header("Location: reservaConfirmada.php?id=" . $reserveId); 
    exit;

?>

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

    // Collect data from the form
    $date = $_POST["date"] ?? "";
    $time = $_POST["time"] ?? "";
    $people = isset($_POST["people"]) ? (int)$_POST["people"] : 0;

    // Basic validation
    if (empty($date) || empty($time) || $people < 1 || $people > 30) {
        header("Location: /contact.php?error=invalid_data");
        exit;
    }

    // Validate date format YYYY-MM-DD
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        header("Location: /contact.php?error=invalid_date");
        exit;
    }

    // Validate that the date exists in the calendar
    $datePieces = explode("-", $date);
    if (!checkdate((int)$datePieces[1], (int)$datePieces[2], (int)$datePieces[0])) {
        header("Location: /contact.php?error=invalid_date");
        exit;
    }

    // Validate that it is not a past date
    $today = new DateTime("today");
    $reservationDate = new DateTime($date);

    if ($reservationDate < $today) {
        header("Location: /contact.php?error=past_date");
        exit;
    }

    // If reservation is for today, ensure the selected hour is not in the past
    if ($reservationDate == $today) {
        $currentHour = (new DateTime())->format("H:i");

        if ($time <= $currentHour) {
            header("Location: /contact.php?error=invalid_time");
            exit;
        }
    }

    // Validate that it is not Sunday
    if ($reservationDate->format("w") == 0) { // 0 = Sunday
        header("Location: /contact.php?error=sunday");
        exit;
    }

    // Validate allowed hours
    $allowedHours = [
        "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
        "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
    ];

    if (!in_array($time, $allowedHours)) {
        header("Location: /contact.php?error=invalid_time");
        exit;
    }

    // Validate maximum capacity (50 people per hour)
    $maximumCapacity = 50;

    $query = $db->prepare("SELECT SUM(num_personas) AS total FROM reservas WHERE fecha = :date AND hora = :time");
    $query->execute([
        ":date" => $date,
        ":time" => $time
    ]);

    $totalCurrent = (int)$query->fetch(PDO::FETCH_ASSOC)["total"];
    $totalAfterReservation = $totalCurrent + $people;

    if ($totalAfterReservation > $maximumCapacity) {
        header("Location: /contact.php?error=fully_booked");
        exit;
    }

    // Insert booking
    $insert = $db->prepare("INSERT INTO reservas (id_usuario, fecha, hora, num_personas) VALUES (:user_id, :date, :time, :people)");

    $insert->execute([
        ":user_id" => $userId,
        ":date" => $date,
        ":time" => $time,
        ":people" => $people
    ]);

    // Retrieve the generated ID and redirect to confirmation page
    $bookingId = $db->lastInsertId(); 
    header("Location: /bookings/booking_confirmation.php?id=" . $bookingId); 
    exit;

?>
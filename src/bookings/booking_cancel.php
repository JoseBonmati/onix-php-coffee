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
    $role = $_SESSION["role"] ?? "user";

    // Check that a booking ID is received
    if (!isset($_GET["id"])) {
        header("Location: /bookings/booking_list.php?error=booking_not_found");
        exit;
    }

    $bookingId = (int) $_GET["id"];

    // Use aliases to map properties consistently
    $sql = $db->prepare("SELECT id, id_usuario AS user_id, fecha AS date, hora AS time, num_personas AS people, estado AS status FROM reservas WHERE id = :id");
    $sql->execute([":id" => $bookingId]);
    $booking = $sql->fetch(PDO::FETCH_ASSOC);

    // Check that it exists
    if (!$booking) {
        header("Location: /bookings/booking_list.php?error=booking_not_found");
        exit;
    }

    // Check permissions
    if ($role === "user" && $booking["user_id"] != $userId) {
        header("Location: /bookings/booking_list.php?error=unauthorized");
        exit;
    }

    // Check that the date has not passed.
    $today = strtotime(date("Y-m-d"));
    $reservationDate = strtotime($booking["date"]);

    if ($reservationDate < $today) {
        header("Location: /bookings/booking_list.php?error=not_cancellable");
        exit;
    }

    // If the reservation is today, ensure the hour has not already passed
    if ($reservationDate == $today) {
        $currentHour = (new DateTime())->format("H:i");

        if ($booking["time"] <= $currentHour) {
            header("Location: /bookings/booking_list.php?error=not_cancellable");
            exit;
        }
    }

    // Check status
    if ($booking["status"] === "cancelada") {
        header("Location: /bookings/booking_list.php?error=not_cancellable");
        exit;
    }

    // Users can only cancel if it is confirmed
    if ($role === "user" && $booking["status"] !== "confirmada") {
        header("Location: /bookings/booking_list.php?error=not_cancellable");
        exit;
    }

    // Cancel reserve
    $update = $db->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = :id");
    $update->execute([":id" => $bookingId]);

    header("Location: /bookings/booking_list.php?cancelled_booking=1");
    exit;

?>
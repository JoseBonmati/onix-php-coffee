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
    $role = $_SESSION["rol"];

    // Check that a reserve ID is received
    if (!isset($_GET["id"])) {
        header("Location: reservaConsulta.php?error=reservaNoExiste");
        exit;
    }

    $reserveId = (int) $_GET["id"];

    $sql = $con->prepare("SELECT id, id_usuario, fecha, hora, num_personas, estado FROM reservas WHERE id = :id");
    $sql->execute([":id" => $reserveId]);
    $reserve = $sql->fetch();

    // Check that it exists
    if (!$reserve) {
        header("Location: reservaConsulta.php?error=reservaNoExiste");
        exit;
    }

    // Check permissions
    if ($role === "usuario" && $reserve["id_usuario"] != $userId) {
        header("Location: reservaConsulta.php?error=noAutorizado");
        exit;
    }

    // Check that the date has not passed.
    $today = strtotime(date("Y-m-d"));
    $reservationDate = strtotime($reserve["fecha"]);

    if ($reservationDate < $today) {
        header("Location: reservaConsulta.php?error=noCancelable");
        exit;
    }

    // If the reservation is today, ensure the hour has not already passed
    if ($reservationDate == $today) {
        $currentHour = (new DateTime())->format("H:i");

        if ($reserve["hora"] <= $currentHour) {
            header("Location: reservaConsulta.php?error=noCancelable");
            exit;
        }
    }

    // Check status
    if ($reserve["estado"] === "cancelada") {
        header("Location: reservaConsulta.php?error=noCancelable");
        exit;
    }

    // Users can only cancel if it is confirmed
    if ($role === "usuario" && $reserve["estado"] !== "confirmada") {
        header("Location: reservaConsulta.php?error=noCancelable");
        exit;
    }

    // Cancel reserve
    $update = $con->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = :id");

    $update->execute([":id" => $reserveId]);

    header("Location: reservaConsulta.php?reservaCancelada=1");
    exit;

?>

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

    // Get reservation ID
    if (!isset($_GET["id"])) {
        header("Location: /bookings/booking_list.php?error=booking_not_found");
        exit;
    }

    $bookingId = (int) $_GET["id"];

    // Fetch reservation with English aliases
    $sql = $db->prepare("SELECT id, id_usuario AS user_id, fecha AS date, hora AS time, num_personas AS people, estado AS status FROM reservas WHERE id = :id");
    $sql->execute([":id" => $bookingId]);
    $booking = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header("Location: /bookings/booking_list.php?error=booking_not_found");
        exit;
    }

    // Permission check
    if ($role === "user" && $booking["user_id"] != $userId) {
        header("Location: /bookings/booking_list.php?error=unauthorized");
        exit;
    }

    // Date check
    $today = strtotime(date("Y-m-d"));
    $reserveDate = strtotime($booking["date"]);

    if ($reserveDate < $today) {
        header("Location: /bookings/booking_list.php?error=not_editable");
        exit;
    }
    // If reservation is today, ensure the hour has not already passed
    if ($reserveDate == $today) {
        $currentHour = date("H:i");

        if ($booking["time"] <= $currentHour) {
            header("Location: /bookings/booking_list.php?error=not_editable");
            exit;
        }
    }

    // User restrictions
    if ($role === "user" && $booking["status"] !== "pendiente") {
        header("Location: /bookings/booking_list.php?error=not_editable");
        exit;
    }

    // Handle form submission
    $errorMessages = [];

    if (isset($_POST["edit_submit"]) || isset($_POST["status"])) {
        $date = trim($_POST["date"] ?? "");
        $time = trim($_POST["time"] ?? "");
        $people = isset($_POST["people"]) ? (int) $_POST["people"] : 0;
        $status = $_POST["status"] ?? $booking["status"];

        // Users can only cancel their own pending reservation
        if ($role === "user") {
            if (isset($_POST["status"]) && $_POST["status"] === "cancelada" && $booking["status"] === "pendiente") {
                $status = "cancelada";
            } else {
                $status = $booking["status"];
            }
        }

        // Basic validation
        if ($date === "" || $time === "" || $people < 1) {
            $errorMessages[] = "Debes completar todos los campos correctamente.";
        }

        // Validate maximum number of people allowed
        if ($people > 30) {
            $errorMessages[] = "El número de personas no es válido (máximo 30).";
        }

        // Validate date format
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $errorMessages[] = "La fecha no es válida.";
        } else {
            // Validate date exists
            $datePieces = explode("-", $date);
            if (!checkdate((int)$datePieces[1], (int)$datePieces[2], (int)$datePieces[0])) {
                $errorMessages[] = "La fecha no existe.";
            }

            // Validate not past
            $newDate = strtotime($date);
            if ($newDate < $today) {
                $errorMessages[] = "No puedes seleccionar una fecha pasada.";
            }

            // If the new date is today, validate that the new hour has not already passed
            if ($newDate == $today) {
                $currentHour = date("H:i");
                if ($time <= $currentHour) {
                    $errorMessages[] = "La hora seleccionada ya ha pasado.";
                }
            }
        }

        // Validate allowed hours
        $allowedHours = [
            "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
            "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
        ];

        if (!in_array($time, $allowedHours)) {
            $errorMessages[] = "La hora seleccionada no es válida.";
        }

        // Validate capacity
        if (empty($errorMessages)) {

            $query = $db->prepare("SELECT SUM(num_personas) AS total FROM reservas WHERE fecha = :date AND hora = :time AND id != :id");
            $query->execute([
                ":date" => $date,
                ":time" => $time,
                ":id" => $bookingId
            ]);

            $currentTotal = (int) $query->fetch(PDO::FETCH_ASSOC)["total"];
            $maxCapacity = 50;

            if ($currentTotal + $people > $maxCapacity) {
                $errorMessages[] = "No quedan plazas disponibles a esa hora.";
            }
        }

        // If no errors → update reservation
        if (empty($errorMessages)) {

            $update = $db->prepare("UPDATE reservas SET fecha = :date, hora = :time, num_personas = :people, estado = :status WHERE id = :id");

            $update->execute([
                ":date" => $date,
                ":time" => $time,
                ":people" => $people,
                ":status" => $status,
                ":id" => $bookingId
            ]);

            header("Location: /bookings/booking_list.php?updated_booking=1");
            exit;
        }
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="onix-bg d-flex justify-content-center align-items-start py-5">
        <div class="onix-card p-4" style="max-width: 700px;">

            <h2 class="text-center fw-bold mb-4">Editar reserva</h2>

            <!-- Errors -->
            <div class="mb-3">
                <?php
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <div id="errors" class="mb-3 text-danger fw-semibold"></div>

            <form method="post" action="/bookings/booking_edit.php?id=<?= $bookingId ?>">

                <div class="mb-3">
                    <label for="date" class="form-label fw-semibold">Fecha</label>
                    <input type="date" name="date" id="date" class="form-control onix-input" value="<?= htmlspecialchars((string)$booking["date"]) ?>">
                </div>

                <div class="mb-3">
                    <label for="time" class="form-label fw-semibold">Hora</label>
                    <select name="time" id="time" class="form-select onix-input">
                        <option>Cargando horas...</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="people" class="form-label fw-semibold">Personas</label>
                    <select id="people" name="people" class="form-select onix-input">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?= $i ?>" <?= ($i == $booking["people"]) ? "selected" : "" ?>>
                                <?= $i ?> persona<?= $i > 1 ? "s" : "" ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <?php if ($role === "admin"): ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cambiar estado</label>
                        <div class="d-flex gap-3">

                        <?php if ($booking["status"] === "pendiente"): ?>
                            <button type="submit" name="status" value="confirmada" class="btn btn-success fw-semibold"
                                onclick="return confirm('¿Confirmar esta reserva?');">
                                Confirmar
                            </button>

                            <button type="submit" name="status" value="cancelada" class="btn btn-danger fw-semibold"
                                onclick="return confirm('¿Cancelar esta reserva?');">
                                Cancelar
                            </button>

                        <?php elseif ($booking["status"] === 'confirmada'): ?>
                            <button type="submit" name="status" value="cancelada" class="btn btn-danger fw-semibold"
                                onclick="return confirm('¿Cancelar esta reserva?');">
                                Cancelar
                            </button>

                        <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === "user" && $booking["status"] === "pendiente"): ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cancelar reserva</label>
                        <div class="d-flex gap-3">

                            <button type="submit" name="status" value="cancelada" class="btn btn-danger fw-semibold"
                                onclick="return confirm('¿Seguro que quieres cancelar esta reserva?');">
                                Cancelar reserva
                            </button>

                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3 mt-4">
                    <button type="submit" name="edit_submit" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <a href="/bookings/booking_list.php" class="btn btn-outline-secondary">Volver</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectTime = document.getElementById("time");
            let currentTime = "<?= htmlspecialchars((string)$booking["time"]) ?>";

            if (currentTime.length >= 5) {
                currentTime = currentTime.slice(0, 5);
            }

            const hours = [
                "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
                "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
            ];

            selectTime.innerHTML = "";

            hours.forEach(h => {
                const opt = document.createElement("option");
                opt.value = h;
                opt.textContent = h;

                if (h === currentTime) {
                    opt.selected = true;
                }

                selectTime.appendChild(opt);
            });
        });
    </script>

    <script src="/bookings/bookings_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
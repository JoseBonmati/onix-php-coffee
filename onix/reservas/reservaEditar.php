<?php

    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: ../usuarios/login.php?acceso=denegado");
        exit;
    }

    $userId = $_SESSION["id"];
    $role = $_SESSION["rol"];

    // Get reservation ID
    if (!isset($_GET["id"])) {
        header("Location: reservaConsulta.php?error=reservaNoExiste");
        exit;
    }

    $reserveId = (int) $_GET["id"];

    // Fetch reservation
    $sql = $con->prepare("SELECT id, id_usuario, fecha, hora, num_personas, estado FROM reservas WHERE id = :id");
    $sql->execute([":id" => $reserveId]);
    $reserve = $sql->fetch();

    if (!$reserve) {
        header("Location: reservaConsulta.php?error=reservaNoExiste");
        exit;
    }

    // Permission check
    if ($role === "usuario" && $reserve["id_usuario"] != $userId) {
        header("Location: reservaConsulta.php?error=noAutorizado");
        exit;
    }

    // Date check
    $today = strtotime(date("Y-m-d"));
    $reserveDate = strtotime($reserve["fecha"]);

    if ($reserveDate < $today) {
        header("Location: reservaConsulta.php?error=noEditable");
        exit;
    }
    // If reservation is today, ensure the hour has not already passed
    if ($reserveDate == $today) {
        $currentHour = date("H:i");

        if ($reserve["hora"] <= $currentHour) {
            header("Location: reservaConsulta.php?error=noEditable");
            exit;
        }
    }

    // User restrictions
    if ($role === "usuario" && $reserve["estado"] !== "pendiente") {
        header("Location: reservaConsulta.php?error=noEditable");
        exit;
    }

    // Handle form submission
    $errorMessages = [];

    if (isset($_POST["enviar"]) || isset($_POST["estado"])) {
        $date = $_POST["fecha"] ?? "";
        $hour = $_POST["hora"] ?? "";
        $people = isset($_POST["personas"]) ? (int) $_POST["personas"] : 0;
        $status = $_POST["estado"] ?? $reserve["estado"];

        // Users can only cancel their own pending reservation
        if ($role === "usuario") {
            if (isset($_POST["estado"]) && $_POST["estado"] === "cancelada" && $reserve["estado"] === "pendiente") {
                $status = "cancelada";
            } else {
                $status = $reserve["estado"];
            }
        }

        // Basic validation
        if ($date === "" || $hour === "" || $people < 1) {
            $errorMessages[] = "Debes completar todos los campos correctamente.";
        }

        // Validate maximum number of people allowed
        if ($people > 30) {
            $errorMessages[] = "El número de personas no es válido.";
        }

        // Validate date format
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $errorMessages[] = "La fecha no es válida.";
        }

        // Validate date exists
        $datePieces = explode("-", $date);
        if (!checkdate($datePieces[1], $datePieces[2], $datePieces[0])) {
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
            if ($hour <= $currentHour) {
                $errorMessages[] = "La hora seleccionada ya ha pasado.";
            }
        }

        // Validate allowed hours
        $allowedHours = [
            "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
            "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
        ];

        if (!in_array($hour, $allowedHours)) {
            $errorMessages[] = "La hora seleccionada no es válida.";
        }

        // Validate capacity
        if (empty($errorMessages)) {

            $query = $con->prepare("SELECT SUM(num_personas) AS total FROM reservas WHERE fecha = :fecha AND hora = :hora AND id != :id");

            $query->execute([
                ":fecha" => $date,
                ":hora" => $hour,
                ":id" => $reserveId
            ]);

            $currentTotal = (int) $query->fetch()["total"];
            $maxCapacity = 50;

            if ($currentTotal + $people > $maxCapacity) {
                $errorMessages[] = "No quedan plazas disponibles a esa hora.";
            }
        }

        // If no errors → update reservation
        if (empty($errorMessages)) {

            $update = $con->prepare("UPDATE reservas SET fecha = :fecha, hora = :hora, num_personas = :personas, estado = :estado WHERE id = :id");

            $update->execute([
                ":fecha" => $date,
                ":hora" => $hour,
                ":personas" => $people,
                ":estado" => $status,
                ":id" => $reserveId
            ]);

            header("Location: reservaConsulta.php?reservaEditada=1");
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
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
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

            <!-- Client-side errors -->
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>

            <form method="post" action="reservaEditar.php?id=<?= $reserveId ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Fecha</label>
                    <input type="date" name="fecha" id="fecha" class="form-control onix-input" value="<?= htmlspecialchars($reserve["fecha"]) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Hora</label>
                    <select name="hora" id="hora" class="form-select onix-input">
                        <option>Cargando horas...</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Personas</label>
                    <select id="personas" name="personas" class="form-select onix-input">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?= $i ?>" <?= ($i == $reserve["num_personas"]) ? "selected" : "" ?>>
                                <?= $i ?> persona<?= $i > 1 ? "s" : "" ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <?php if ($role === "administrador"): ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cambiar estado</label>
                        <div class="d-flex gap-3">

                        <?php if ($reserve["estado"] === "pendiente"): ?>
                            <button type="submit" name="estado" value="confirmada" class="btn btn-success fw-semibold"
                                onclick="return confirm('¿Confirmar esta reserva?');">
                                Confirmar
                            </button>

                            <button type="submit" name="estado" value="cancelada" class="btn btn-danger fw-semibold"
                                onclick="return confirm('¿Cancelar esta reserva?');">
                                Cancelar
                            </button>

                        <?php elseif ($reserve["estado"] === 'confirmada'): ?>
                            <button type="submit" name="estado" value="cancelada" class="btn btn-danger fw-semibold"
                                onclick="return confirm('¿Cancelar esta reserva?');">
                                Cancelar
                            </button>

                        <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === "usuario" && $reserve["estado"] === "pendiente"): ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cancelar reserva</label>
                        <div class="d-flex gap-3">

                            <button type="submit" name="estado" value="cancelada" class="btn btn-danger fw-semibold"
                                onclick="return confirm('¿Seguro que quieres cancelar esta reserva?');">
                                Cancelar reserva
                            </button>

                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3 mt-4">
                    <button type="submit" name="enviar" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <a href="reservaConsulta.php" class="btn btn-outline-secondary">Volver</a>
                </div>

            </form>
        </div>
    </div>

    <script>

        document.addEventListener("DOMContentLoaded", function () {
            const selectHour = document.getElementById("hora");
            let currentHour = "<?= $reserve["hora"] ?>";

            if (currentHour.length >= 5) {
                currentHour = currentHour.slice(0, 5);
            }

            const hours = [
                "07:00","08:00","09:00","10:00","11:00","12:00","13:00","14:00",
                "16:00","17:00","18:00","19:00","20:00","21:00","22:00"
            ];

            selectHour.innerHTML = "";

            hours.forEach(h => {
                const opt = document.createElement("option");
                opt.value = h;
                opt.textContent = h;

                if (h === currentHour) {
                    opt.selected = true;
                }

                selectHour.appendChild(opt);
            });
        });

    </script>

    <script src="reservasValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

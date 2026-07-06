<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    // Restrict access: only administrators
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $errorMessages = [];

    // Retrieve Category ID from POST or GET requests safely
    if (isset($_POST["edit_submit"]) || isset($_POST["toggle_status"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
    }

    // Process category activation/deactivation toggle
    if (isset($_POST["toggle_status"])) {
        $currentStatus = $_POST["current_status"] ?? "activo";
        $newStatus = ($currentStatus === "activo") ? "inactivo" : "activo";

        $statusStmt = $db->prepare("UPDATE categorias SET estado = :status WHERE id = :id");
        $statusStmt->execute([
            ":status" => $newStatus,
            ":id" => $id
        ]);

        header("Location: /categories/category_edit.php?id=$id");
        exit;
    }

    // Process category basic data update form
    if (isset($_POST["edit_submit"])) {
        $name = trim($_POST["name"] ?? "");

        // Input data validation
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Check for unique name constraints excluding the current entry
        if (empty($errorMessages)) {
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :name AND id != :id");
            $checkStmt->execute([
                ":name" => $name,
                ":id" => $id
            ]);

            if ($checkStmt->fetchColumn() > 0) {
                $errorMessages[] = "Ya existe otra categoría con ese nombre.";
            }
        }

        // Persist update if no errors are present
        if (empty($errorMessages)) {
            $updateStmt = $db->prepare("UPDATE categorias SET nombre = :name WHERE id = :id");
            $updateStmt->execute([
                ":name" => $name,
                ":id" => $id
            ]);

            header("Location: /categories/category_list.php?updated_category=" . urlencode($name));
            exit;
        }
    }

    // Fetch current data state for category formulation
    $query = $db->prepare("SELECT id, nombre, estado FROM categorias WHERE id = :id");
    $query->execute([":id" => $id]);

    if ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $fetchedName = $row["nombre"];
        $fetchedStatus = $row["estado"];
    } else {
        $errorMessages[] = "No se ha encontrado la categoría con el ID proporcionado.";
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg">
        <div class="p-4 onix-card" style="max-width: 600px;">
            <h2 class="text-center mb-4 fw-bold">Editar categoría</h2>

            <!-- Server-side errors -->
            <div class="mb-3">
                <?php
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client-side errors -->
            <div id="errors" class="mb-3 text-danger fw-semibold"></div>

            <?php if (empty($errorMessages) || isset($_POST["edit_submit"])): ?>

                <form name="edit_form" id="edit_form" method="post" action="/categories/category_edit.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
                    <p class="mb-4 text-center fw-semibold">Modifica los datos de la categoría y guarda los cambios.</p>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nombre de la categoría</label>
                        <input type="text" name="name" id="name" class="form-control onix-input" maxlength="100" value="<?= htmlspecialchars($fetchedName ?? '') ?>">
                    </div>

                    <div class="d-grid gap-3 mt-4">
                        <button type="submit" name="edit_submit" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    </div>
                </form>

                <!-- Independent form for status toggling -->
                <form id="toggle_form" method="post" action="/categories/category_edit.php" class="mt-3">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
                    <input type="hidden" name="current_status" value="<?= htmlspecialchars($fetchedStatus ?? '') ?>">

                    <div class="d-grid gap-3">
                        <button type="submit" name="toggle_status"
                                class="btn <?= (isset($fetchedStatus) && $fetchedStatus === 'activo') ? 'btn-danger' : 'btn-success' ?> fw-semibold w-100">
                            <?= (isset($fetchedStatus) && $fetchedStatus === 'activo') ? 'Desactivar categoría' : 'Activar categoría' ?>
                        </button>
                        <hr class="onix-divider">
                        <a href="/categories/category_list.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>
    
    <script src="/categories/categories_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
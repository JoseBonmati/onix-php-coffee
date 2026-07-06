<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    // Restrict access: only administrators can view this page
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $errorMessages = [];

    if (isset($_POST["create_submit"])) {
        $name = trim($_POST["name"] ?? "");

        // Validate name
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Check if category already exists
        if (empty($errorMessages)) {
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :name");
            $checkStmt->execute([":name" => $name]);

            if ($checkStmt->fetchColumn() > 0) {
                $errorMessages[] = "La categoría ya está registrada, use otro nombre.";
            }
        }

        // Insert new category if no errors
        if (empty($errorMessages)) {
            $insertStmt = $db->prepare("INSERT INTO categorias (nombre, estado) VALUES (:name, 'activo')");
            $insertStmt->execute([":name" => $name]);

            if ($insertStmt->rowCount() > 0) {
                header("Location: /categories/category_list.php?created_category=" . urlencode($name));
                exit;
            } else {
                $errorMessages[] = "Ha ocurrido un error con la base de datos.";
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg">
        <div class="p-4 onix-card" style="max-width: 500px;">
            
            <h2 class="text-center mb-4 fw-bold">Crear categoría</h2>

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

            <form name="create_form" id="create_form" method="post" action="/categories/category_create.php">
                <p class="mb-3 text-center fw-semibold">Rellena los siguientes datos para crear una nueva categoría.</p>

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre de la categoría</label>
                    <input type="text" class="form-control onix-input" name="name" id="name" maxlength="100" 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="create_submit">Crear categoría</button>
                    <hr class="onix-divider">
                    <a href="/categories/category_list.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="/categories/categories_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
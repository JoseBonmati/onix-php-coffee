<?php

    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can view this page
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");

        // Validate name
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Check if category already exists
        if (empty($errorMessages)) {
            $check = $con->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre");
            $check->execute([":nombre" => $name]);

            if ($check->fetchColumn() > 0) {
                $errorMessages[] = "La categoría ya está registrada, use otro nombre.";
            }
        }

        // Insert new category if no errors
        if (empty($errorMessages)) {
            $stmt = $con->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
            $stmt->execute([":nombre" => $name]);

            if ($stmt->rowCount() > 0) {
                header("Location: categoriaConsulta.php?nameN=" . urlencode($name));
                exit;
            } else {
                $errorMessages[] = "Ha ocurrido un error con la base de datos";
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
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
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
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>

            <form name="fCreacion" id="fCreacion" method="post" action="categoriaCrear.php">
                <p class="mb-3 text-center fw-semibold">Rellena los siguientes datos para crear una nueva categoría.</p>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la categoría</label>
                    <input type="text" class="form-control onix-input" name="nombre" id="nombre" maxlength="100" value="<?php if(isset($_POST['nombre'])) echo htmlspecialchars($_POST['nombre']); ?>">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="enviar">Crear categoría</button>
                    <hr class="onix-divider">
                    <a href="categoriaConsulta.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="categoriasValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

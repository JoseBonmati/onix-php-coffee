<?php

    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can delete products
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    $name = "";
    $image = "";
    $id = null;

    // Retrieve product data by ID
    if (isset($_GET["id"])) {
        $id = (int) $_GET["id"];

        $query = $con->prepare("SELECT nombre, imagen FROM productos WHERE id = :id");
        $query->execute([":id" => $id]);

        if ($data = $query->fetch()) {
            $name = $data["nombre"];
            $image = $data["imagen"];
        } else {
            $errorMessages[] = "No se ha encontrado el producto con el ID proporcionado.";
        }
    }

    // Handle deletion request
    if (isset($_POST["eliminar"])) {
        $id = (int) $_POST["id"];

        $checkQuery = $con->prepare("SELECT nombre, imagen FROM productos WHERE id = :id");
        $checkQuery->execute([":id" => $id]);

        if ($data = $checkQuery->fetch()) {
            $name = $data["nombre"];
            $image = $data["imagen"];

            // Delete image file if exists
            if (!empty($image) && file_exists($image)) {
                unlink($image);
            }

            $deleteQuery = $con->prepare("DELETE FROM productos WHERE id = :id");
            $deleteQuery->execute([":id" => $id]);

            if ($deleteQuery->rowCount() > 0) {
                header("Location: productoConsulta.php?nameD=" . urlencode($name));
                exit;
            } else {
                $errorMessages[] = "No se ha podido eliminar el producto.";
            }
        } else {
            $errorMessages[] = "El producto indicado no existe.";
        }
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg">
        <div class="p-4 onix-card" style="max-width: 600px;">
            <h2 class="text-center mb-4 fw-bold">Eliminar producto</h2>

            <!-- Error messages -->
            <div class="mb-3">
                <?php
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <?php if (empty($errorMessages) && $id !== null): ?>
                <p class="text-center fw-semibold mb-4">
                    ¿Desea eliminar el producto<br>
                    <span class="text-onix fw-bold"><?= htmlspecialchars($name) ?></span>?
                </p>

                <?php if (!empty($image)): ?>
                    <div class="text-center mb-3">
                        <img src="<?= htmlspecialchars($image) ?>" class="img-thumbnail" style="max-height: 150px;">
                    </div>
                <?php endif; ?>

                <form name="fBorrado" id="fBorrado" method="post" action="productoEliminar.php">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                    <div class="d-grid gap-3">
                        <button type="submit" name="eliminar" class="btn btn-danger fw-semibold" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">
                            Eliminar producto
                        </button>
                        <hr class="onix-divider">
                        <a href="productoConsulta.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

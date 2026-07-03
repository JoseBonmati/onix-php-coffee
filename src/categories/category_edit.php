<?php

    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    // Get category ID from POST or GET
    if (isset($_POST["enviar"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
    }

    // Handle form submission (update category name)
    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");

        // Validations
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Check for duplicate category name
        if (empty($errorMessages)) {
            $check = $con->prepare("SELECT COUNT(*) FROM categorias WHERE nombre = :nombre AND id != :id");
            $check->execute([
                ":nombre" => $name,
                ":id" => $id
            ]);
            if ($check->fetchColumn() > 0) {
                $errorMessages[] = "Ya existe otra categoría con ese nombre.";
            }
        }

        // Base SQL update
        $sql = "UPDATE categorias SET nombre = :nombre WHERE id = :id";
        $params = [
            ":nombre" => $name,
            ":id" => $id
        ];

        // Execute update if no errors
        if (empty($errorMessages)) {
            $updateQuery = $con->prepare($sql);
            $updateQuery->execute($params);

            if ($updateQuery->rowCount() >= 0) {
                header("Location: categoriaConsulta.php?nameE=" . urlencode($name));
                exit;
            } else {
                $errorMessages[] = "No se ha podido actualizar la categoría en la base de datos.";
            }
        }
    }

    // Activate / Deactivate product
    if (isset($_POST["cambiarEstado"])) {
        $id = (int)$_POST["id"];
        $newStatus = $_POST["estado"] === "activo" ? "inactivo" : "activo";

        $updateStatus = $con->prepare("UPDATE categorias SET estado = :estado WHERE id = :id");
        $updateStatus->execute([
            ":estado" => $newStatus,
            ":id" => $id
        ]);

        header("Location: categoriaEditar.php?id=" . urlencode($id) . "&estadoCambiado=" . urlencode($newStatus));
        exit;
    }

    // Retrieve category data for form
    $cat = $con->prepare("SELECT * FROM categorias WHERE id = :id");
    $cat->execute([":id" => $id]);
    $category = $cat->fetch();

    if (!$category) {
        $errorMessages[] = "No se ha encontrado la categoria";
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
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
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
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>

            <?php if ($category): ?>

            <form name="fEdicion" id="fEdicion" method="post" action="categoriaEditar.php">
                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                <p class="mb-4 text-center fw-semibold">Modifica los datos de la categoría y guarda los cambios.</p>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre de la categoría</label>
                    <input type="text" name="nombre" id="nombre" class="form-control onix-input" maxlength="100" value="<?= $category['nombre'] ?>">
                </div>

                <div class="d-grid gap-3 mt-4">

                    <!-- Activate/deactivate button -->
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <input type="hidden" name="estado" value="<?= $category['estado'] ?>">

                        <?php if ($category['estado'] === "activo"): ?>
                            <button type="submit" name="cambiarEstado" class="btn btn-danger fw-semibold">
                                Desactivar categoria
                            </button>
                        <?php else: ?>
                            <button type="submit" name="cambiarEstado" class="btn btn-success fw-semibold">
                                Activar categoria
                            </button>
                        <?php endif; ?>
                    </form>

                    <button type="submit" name="enviar" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <hr class="onix-divider">
                    <a href="categoriaConsulta.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="categoriasValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

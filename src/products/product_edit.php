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

    // Retrieve Product ID securely
    if (isset($_POST["edit_submit"]) || isset($_POST["toggle_status"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
    }

    // Process product activation/deactivation toggle
    if (isset($_POST["toggle_status"])) {
        $currentStatus = $_POST["current_status"] ?? "activo";
        $newStatus = ($currentStatus === "activo") ? "inactivo" : "activo";

        $updateStatusStmt = $db->prepare("UPDATE productos SET estado = :status WHERE id = :id");
        $updateStatusStmt->execute([
            ":status" => $newStatus,
            ":id" => $id
        ]);

        header("Location: /products/product_edit.php?id=" . urlencode((string)$id) . "&status_toggled=1");
        exit;
    }

    // Process form submission for editing product data
    if (isset($_POST["edit_submit"])) {
        $name = trim($_POST["name"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $price = trim($_POST["price"] ?? "");
        $categoryId = trim($_POST["category_id"] ?? "");

        // Input Validations
        if ($name === "") $errorMessages[] = "El campo Nombre no puede estar vacío.";
        if ($description === "") $errorMessages[] = "El campo Descripción no puede estar vacío.";

        if ($price === "") {
            $errorMessages[] = "El campo Precio no puede estar vacío.";
        } elseif (!preg_match("/^[0-9]*\.?[0-9]+$/", $price)) {
            $errorMessages[] = "El formato del precio no es válido.";
        }

        if ($categoryId === "") $errorMessages[] = "Debe seleccionar una categoría.";

        // Image validation (Only if a new image is uploaded)
        $newImageUploaded = !empty($_FILES["image"]["tmp_name"]);
        if ($newImageUploaded) {
            $file = $_FILES["image"];
            $tempName = $file["tmp_name"];
            $realName = $file["name"];
            $size = $file["size"];

            $maxSize = 2 * 1024 * 1024;
            if ($size > $maxSize) $errorMessages[] = "La imagen es demasiado grande (máx 2MB).";

            $imgInfo = getimagesize($tempName);
            if (!$imgInfo) {
                $errorMessages[] = "El archivo no es una imagen válida.";
            } else {
                $width = $imgInfo[0];
                $height = $imgInfo[1];
                $ext = strtolower(pathinfo($realName, PATHINFO_EXTENSION));

                if (!in_array($ext, ["jpg","jpeg","png"])) {
                    $errorMessages[] = "Formato no permitido (solo jpg, jpeg, png).";
                }

                if ($width > 600 || $height > 700) {
                    $errorMessages[] = "La imagen no puede superar 600x700 píxeles.";
                }

                // Standardized DB image path for absolute rendering
                $dbImagePath = "assets/images/" . basename($realName);
                
                $checkImgStmt = $db->prepare("SELECT id FROM productos WHERE imagen = :image AND id != :id");
                $checkImgStmt->execute([
                    ":image" => $dbImagePath,
                    ":id" => $id
                ]);
                if ($checkImgStmt->fetch()) {
                    $errorMessages[] = "Ya existe una imagen con ese nombre.";
                }
            }
        }

        // Proceed with update if there are no validation errors
        if (empty($errorMessages)) {

            $sql = "UPDATE productos SET nombre = :name, descripcion = :description, precio = :price, id_categoria = :category";

            $params = [
                ":name" => $name,
                ":description" => $description,
                ":price" => $price,
                ":category" => $categoryId,
                ":id" => $id
            ];

            if ($newImageUploaded) {
                $tmpImage = $_FILES["image"]["tmp_name"];
                $imgName = basename($_FILES["image"]["name"]);
                
                $targetDir = __DIR__ . "/../assets/images/";
                $dbImagePath = "assets/images/" . $imgName;
                $serverPath = $targetDir . $imgName;

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true); 
                }

                // Delete old image dynamically from the filesystem
                $oldQuery = $db->prepare("SELECT imagen AS image FROM productos WHERE id = :id");
                $oldQuery->execute([":id" => $id]);
                if ($oldRow = $oldQuery->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($oldRow["image"])) {
                        $oldServerPath = __DIR__ . "/../" . $oldRow["image"];
                        if (file_exists($oldServerPath) && is_file($oldServerPath)) {
                            // Supress warning with @ in case OS blocks deletion in Docker volume
                            @unlink($oldServerPath);
                        }
                    }
                }

                // Move new image
                if (move_uploaded_file($tmpImage, $serverPath)) {
                    $sql .= ", imagen = :image";
                    $params[":image"] = $dbImagePath;
                } else {
                    $errorMessages[] = "Error inesperado al guardar la imagen en el servidor.";
                }
            }

            $sql .= " WHERE id = :id";

            if (empty($errorMessages)) {
                $updateStmt = $db->prepare($sql);
                $updateStmt->execute($params);

                if ($updateStmt->rowCount() > 0 || $newImageUploaded) {
                    header("Location: /products/product_list.php?updated_product=" . urlencode($name));
                    exit;
                } else {
                    $errorMessages[] = "No se ha podido actualizar el producto o no hubo cambios reales.";
                }
            }
        }
    }

    // Fetch product data with aliases for straightforward rendering
    $prodQuery = $db->prepare("SELECT id, nombre AS name, descripcion AS description, precio AS price, id_categoria AS category_id, 
                               imagen AS image, estado AS status FROM productos WHERE id = :id");
    $prodQuery->execute([":id" => $id]);
    $product = $prodQuery->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $errorMessages[] = "No se ha encontrado el producto.";
    }

    // Fetch categories with aliases
    $categories = $db->query("SELECT id, nombre AS name FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg py-5">
        <div class="p-4 onix-card" style="max-width: 700px;">
            <h2 class="text-center mb-4 fw-bold">Editar producto</h2>

            <!-- Server-side errors -->
            <div class="mb-3">
                <?php
                    if (isset($_GET["status_toggled"])) {
                        echo "<p class='alert alert-success mb-0'>Estado del producto cambiado.</p>";
                    }
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <div id="errors" class="mb-3 text-danger fw-semibold"></div>

            <?php if ($product): ?>

            <form name="edit_form" id="edit_form" method="post" action="/products/product_edit.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$product['id']) ?>">

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control onix-input" maxlength="100" value="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Descripción</label>
                    <input type="text" name="description" id="description" class="form-control onix-input" maxlength="255" value="<?= htmlspecialchars($product['description']) ?>">
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label fw-semibold">Precio (€)</label>
                    <input type="text" name="price" id="price" class="form-control onix-input" maxlength="10" value="<?= htmlspecialchars((string)$product['price']) ?>">
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label fw-semibold">Categoría</label>
                    <select name="category_id" id="category_id" class="form-select onix-input">
                        <option value="">-- Seleccione una categoría --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars((string)$cat['id']) ?>" <?= ($cat['id'] == $product['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Imagen actual</label><br>
                    <img src="/<?= htmlspecialchars($product['image']) ?>" class="img-thumbnail" style="max-height:150px;" alt="Current Product Image">
                </div>

                <div class="mb-3">
                    <label for="new_image" class="form-label fw-semibold">Nueva imagen (Opcional)</label>
                    <input type="file" name="image" id="new_image" class="form-control onix-input" accept=".jpg, .jpeg, .png">
                </div>

                <div class="d-grid gap-3 mt-4">
                    <button type="submit" name="edit_submit" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <hr class="onix-divider">
                </div>
            </form>

            <form method="post" action="/products/product_edit.php" class="mt-3">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$product['id']) ?>">
                <input type="hidden" name="current_status" value="<?= htmlspecialchars($product['status']) ?>">

                <div class="d-grid gap-3">
                    <?php if ($product['status'] === "activo"): ?>
                        <button type="submit" name="toggle_status" class="btn btn-danger fw-semibold">
                            Desactivar producto
                        </button>
                    <?php else: ?>
                        <button type="submit" name="toggle_status" class="btn btn-success fw-semibold">
                            Activar producto
                        </button>
                    <?php endif; ?>
                    <a href="/products/product_list.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
                </div>
            </form>

            <?php endif; ?>
        </div>
    </div>
    
    <script src="/products/products_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
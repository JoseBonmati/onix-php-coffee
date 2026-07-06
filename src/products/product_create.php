<?php 

    // Session Management
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Database Connection
    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    // Restrict access: only administrators can create products
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    // Array to store server validation error messages
    $errorMessages = [];

    // Process form submission
    if (isset($_POST["create_submit"])) {
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

        // Image upload validation
        if (empty($_FILES["image"]["tmp_name"])) {
            $errorMessages[] = "Debe subir una imagen.";
        } else {
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
                
                $checkImgStmt = $db->prepare("SELECT id FROM productos WHERE imagen = :image");
                $checkImgStmt->execute([":image" => $dbImagePath]);
                if ($checkImgStmt->fetch()) {
                    $errorMessages[] = "Ya existe una imagen con ese nombre en la base de datos.";
                }
            }
        }

        // Insert new product if there are no validation errors
        if (empty($errorMessages)) {
            $tmpImage = $_FILES["image"]["tmp_name"];
            $imgName = basename($_FILES["image"]["name"]);
            $dbImagePath = "assets/images/" . $imgName;
            
            // Calculate absolute server path to move the uploaded file securely
            $targetDir = __DIR__ . "/../assets/images/";
            $serverPath = $targetDir . $imgName;

            // Ensure the directory exists before doing anything else
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true); 
            }

            if (move_uploaded_file($tmpImage, $serverPath)) {

                $insertStmt = $db->prepare("INSERT INTO productos (nombre, id_categoria, descripcion, precio, imagen, estado)
                                            VALUES (:name, :category, :description, :price, :image, 'activo')");

                $insertStmt->execute([
                    ":name" => $name,
                    ":category" => $categoryId,
                    ":description" => $description,
                    ":price" => $price,
                    ":image" => $dbImagePath
                ]);

                if ($insertStmt->rowCount() > 0) {
                    // Redirect to product list with translated success parameter
                    header("Location: /products/product_list.php?created_product=" . urlencode($name));
                    exit;
                } else {
                    $errorMessages[] = "Ha ocurrido un error con la base de datos.";
                }
            } else {
                $errorMessages[] = "Error al guardar la imagen en el servidor.";
            }
        }
    }

    // Fetch categories with aliases
    $catQuery = $db->query("SELECT id, nombre AS name FROM categorias ORDER BY nombre ASC");
    $categories = $catQuery->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg py-5">
        <div class="p-4 onix-card">
            <h2 class="text-center mb-4 fw-bold">Crear producto</h2>

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

            <form name="create_form" id="create_form" method="post" action="/products/product_create.php" enctype="multipart/form-data">
                <p class="mb-3 text-center fw-semibold">Rellena los siguientes datos para crear un nuevo producto.</p>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre del producto</label>
                    <input type="text" class="form-control onix-input" name="name" id="name" maxlength="100" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <input type="text" class="form-control onix-input" name="description" id="description" maxlength="255" value="<?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Precio (€)</label>
                    <input type="text" class="form-control onix-input" name="price" id="price" maxlength="10" value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select name="category_id" id="category_id" class="form-select onix-input">
                        <option value="">-- Seleccione una categoría --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars((string)$cat['id']) ?>"
                                <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Imagen</label>
                    <input type="file" class="form-control onix-input" name="image" id="image" accept=".jpg, .jpeg, .png">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="create_submit">Crear producto</button>
                    <hr class="onix-divider">
                    <a href="/products/product_list.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/products/products_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
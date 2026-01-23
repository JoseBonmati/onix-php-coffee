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

    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");
        $description = trim($_POST["descripcion"] ?? "");
        $price = trim($_POST["precio"] ?? "");
        $categoryId = trim($_POST["categoriaId"] ?? "");

        // Validations
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        if ($description === "") {
            $errorMessages[] = "El campo Descripción no puede estar vacío.";
        }

        if ($price === "") {
            $errorMessages[] = "El campo Precio no puede estar vacío.";
        } elseif (!preg_match("/^[0-9]*\.?[0-9]+$/", $price)) {
            $errorMessages[] = "El formato del precio no es válido. Use números enteros o decimales.";
        }

        if ($categoryId === "") {
            $errorMessages[] = "Debe seleccionar una categoría.";
        }

        // Image validation
        if (empty($_FILES["imagen"]["tmp_name"])) {
            $errorMessages[] = "Debe subir una imagen.";
        } else {
            $file = $_FILES["imagen"];
            $temp_name = $file["tmp_name"];
            $real_name = $file["name"];
            $size = $file["size"];

            $max_size = 2 * 1024 * 1024;
            if ($size > $max_size) $errorMessages[] = "La imagen es demasiado grande (máx 2MB).";

            $img_info = getimagesize($temp_name);
            if (!$img_info) {
                $errorMessages[] = "El archivo no es una imagen válida.";
            } else {
                $width = $img_info[0];
                $height = $img_info[1];
                $ext = strtolower(pathinfo($real_name, PATHINFO_EXTENSION));

                if (!in_array($ext, ["jpg","jpeg","png"])) {
                    $mensajesError[] = "Formato no permitido (solo jpg, jpeg, png).";
                }

                if ($width > 600 || $height > 700) {
                    $errorMessages[] = "La imagen no puede superar 600x700 píxeles.";
                }

                $destinationC = "../assets/imagenes/" . basename($real_name);
                $query = $con->prepare("SELECT id FROM productos WHERE imagen = :imagen");
                $query->execute([":imagen" => $destinationC]);
                if ($query->fetch()) {
                    $errorMessages[] = "Ya existe una imagen con ese nombre en la base de datos.";
                }
            }
        }

        // Insert product if no errors
        if (empty($errorMessages)) {
            $tmpImage = $_FILES["imagen"]["tmp_name"];
            $imgName = $_FILES["imagen"]["name"];
            $destination = "../assets/imagenes/" . $imgName;

            if (move_uploaded_file($tmpImage, $destination)) {

                $stmt = $con->prepare("INSERT INTO productos (nombre, id_categoria, descripcion, precio, imagen) 
                                       VALUES (:nombre, :categoria, :descripcion, :precio, :imagen)");
                $stmt->execute([
                    ":nombre" => $name,
                    ":categoria" => $categoryId,
                    ":descripcion" => $description,
                    ":precio" => $price,
                    ":imagen" => $destination,
                ]);

                if ($stmt->rowCount() > 0) {
                    header("Location: productoConsulta.php?nameN=" . urlencode($name));
                    exit;
                } else {
                    $errorMessages[] = "Ha ocurrido un error con la base de datos.";
                }
            } else {
                $errorMessages[] = "Error inesperado al subir la imagen. Inténtelo de nuevo.";
            }
        }
    }

    // Get categories for dropdown
    $catQuery = $con->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
    $categories = $catQuery->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg">
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
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>

            <form name="fCreacion" id="fCreacion" method="post" action="productoCrear.php" enctype="multipart/form-data">
                <p class="mb-3 text-center fw-semibold">Rellena los siguientes datos para crear un nuevo producto.</p>
                <div class="mb-3">
                    <label class="form-label">Nombre del producto</label>
                    <input type="text" class="form-control onix-input" name="nombre" id="nombre" maxlength="100" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" class="form-control onix-input" name="descripcion" id="descripcion" maxlength="255" value="<?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Precio (€)</label>
                    <input type="text" class="form-control onix-input" name="precio" id="precio" maxlength="10" value="<?= isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoriaId" id="categoriaId" class="form-select onix-input">
                        <option value="">-- Seleccione una categoría --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"
                                <?= (isset($_POST['categoriaId']) && $_POST['categoriaId'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" class="form-control onix-input" name="imagen" id="imagen" accept=".jpg, .jpeg, .png">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="enviar">Crear producto</button>
                    <hr class="onix-divider">
                    <a href="productoConsulta.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="productosValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

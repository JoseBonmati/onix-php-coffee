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

    // Get product ID
    if (isset($_POST["enviar"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
    }

    // Handle form submission
    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");
        $description = trim($_POST["descripcion"] ?? "");
        $price = trim($_POST["precio"] ?? "");
        $categoryId = trim($_POST["categoriaId"] ?? "");

        // Validations
        if ($name === "") $errorMessages[] = "El campo Nombre no puede estar vacío.";
        if ($description === "") $errorMessages[] = "El campo Descripción no puede estar vacío.";
        if ($price === "") {
            $errorMessages[] = "El campo Precio no puede estar vacío.";
        } elseif (!preg_match("/^[0-9]*\.?[0-9]+$/", $price)) {
            $errorMessages[] = "El formato del precio no es válido.";
        }
        if ($categoryId === "") $errorMessages[] = "Debe seleccionar una categoría.";

        // Image handling
        $newImageUploaded = !empty($_FILES["imagen"]["tmp_name"]);
        if ($newImageUploaded) {
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
                    $errorMessages[] = "Ya existe una imagen con ese nombre.";
                }
            }
        }

        // Execute update
        if (empty($errorMessages)) {
            $sql = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, id_categoria = :categoria";
            $params = [
                ":nombre" => $name,
                ":descripcion" => $description,
                ":precio" => $price,
                ":categoria" => $categoryId,
                ":id" => $id
            ];

            if ($newImageUploaded) {
                $tmpImage = $_FILES["imagen"]["tmp_name"];
                $imgName = $_FILES["imagen"]["name"];
                $destination = "../assets/imagenes/" . $imgName;

                // Delete old image
                $oldQuery = $con->prepare("SELECT imagen FROM productos WHERE id = :id");
                $oldQuery->execute([":id" => $id]);
                if ($oldRow = $oldQuery->fetch()) {
                    if (!empty($oldRow["imagen"]) && file_exists($oldRow["imagen"])) {
                        unlink($oldRow["imagen"]);
                    }
                }

                if (move_uploaded_file($tmpImage, $destination)) {
                    $sql .= ", imagen = :imagen";
                    $params[":imagen"] = $destination;
                } else {
                    $errorMessages[] = "Error inesperado al subir la imagen.";
                }
            }

            $sql .= " WHERE id = :id";

            if (empty($errorMessages)) {
                $updateQuery = $con->prepare($sql);
                $updateQuery->execute($params);

                if ($updateQuery->rowCount() > 0) {
                    header("Location: productoConsulta.php?nameE=$name");
                    exit;
                } else {
                    $errorMessages[] = "No se ha podido actualizar el producto.";
                }
            }
        }
    }

    // Activate / Deactivate product
    if (isset($_POST["cambiarEstado"])) {
        $id = (int)$_POST["id"];
        $newStatus = $_POST["estado"] === "activo" ? "inactivo" : "activo";

        $updateStatus = $con->prepare("UPDATE productos SET estado = :estado WHERE id = :id");
        $updateStatus->execute([
            ":estado" => $newStatus,
            ":id" => $id
        ]);

        header("Location: productoEditar.php?id=" . urlencode($id) . "&estadoCambiado=" . urlencode($newStatus));
        exit;
    }

    // Retrieve product data
    $prod = $con->prepare("SELECT * FROM productos WHERE id = :id");
    $prod->execute([":id" => $id]);
    $product = $prod->fetch();

    if (!$product) {
        $errorMessages[] = "No se ha encontrado el producto.";
    }

    // Get categories
    $catQuery = $con->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
    $categories = $catQuery->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg ">
        <div class="p-4 onix-card" style="max-width: 700px;">
            <h2 class="text-center mb-4 fw-bold">Editar producto</h2>

            <!-- Server-side errors -->
            <div class="mb-3">
                <?php
                    if (isset($_GET["estadoCambiado"])) {
                        echo "<p class='alert alert-success mb-0'>Estado del producto cambiado.</p>";
                    }
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client-side errors -->
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>

            <?php if ($product): ?>

            <form name="fEdicion" id="fEdicion" method="post" action="productoEditar.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control onix-input" maxlength="100" value="<?= htmlspecialchars($product['nombre']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion" class="form-control onix-input" maxlength="255" value="<?= htmlspecialchars($product['descripcion']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Precio (€)</label>
                    <input type="text" name="precio" id="precio" class="form-control onix-input" maxlength="10" value="<?= htmlspecialchars($product['precio']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Categoría</label>
                    <select name="categoriaId" id="categoriaId" class="form-select onix-input">
                        <option value="">-- Seleccione una categoría --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"
                                <?= ($cat['id'] == $product['id_categoria']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Imagen actual</label><br>
                    <img src="<?= $product['imagen'] ?>" class="img-thumbnail" style="max-height:150px;">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nueva imagen</label>
                    <input type="file" name="imagen" class="form-control onix-input" accept=".jpg, .jpeg, .png">
                </div>

                <div class="d-grid gap-3 mt-4">

                    <!-- Activate/deactivate button -->
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="estado" value="<?= $product['estado'] ?>">

                        <?php if ($product['estado'] === "activo"): ?>
                            <button type="submit" name="cambiarEstado" class="btn btn-danger fw-semibold">
                                Desactivar producto
                            </button>
                        <?php else: ?>
                            <button type="submit" name="cambiarEstado" class="btn btn-success fw-semibold">
                                Activar producto
                            </button>
                        <?php endif; ?>
                    </form>

                    <button type="submit" name="enviar" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <hr class="onix-divider">
                    <a href="productoConsulta.php" class="btn btn-outline-secondary fw-semibold">Volver</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="productosValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

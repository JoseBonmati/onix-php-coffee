<?php 
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can edit products
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    // Get product ID from POST or GET
    if (isset($_POST["enviar"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
    }

    // Handle form submission (update product data)
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
        } else if (!preg_match("/^[0-9]*\.?[0-9]+$/", $price)) {
            $errorMessages[] = "El formato introducido no es correcto, formato correcto [Números y/o números decimales]";
        }
        if ($categoryId === "") {
            $errorMessages[] = "Debe seleccionar una categoría.";
        }

        // Image handling
        $newImageUploaded = !empty($_FILES["imagen"]["tmp_name"]);
        if ($newImageUploaded) {
            $file = $_FILES["imagen"];
            $temp_name = $file["tmp_name"];
            $real_name = $file["name"];
            $size = $file["size"];

            $max_size = 2 * 1024 * 1024;
            if ($size > $max_size) {
                $errorMessages[] = "La imagen es demasiado grande (máx 2MB)";
            }
            $img_info = getimagesize($temp_name);
            if (!$img_info) {
                $errorMessages[] = "El archivo no es una imagen válida";
            } else {
                $width = $img_info[0];
                $height  = $img_info[1];
                $ext = strtolower(pathinfo($real_name, PATHINFO_EXTENSION));

                $ext_allow = ["jpg","jpeg","png"];
                if (!in_array($ext, $ext_allow)) {
                    $errorMessages[] = "Formato no permitido (solo jpg, jpeg, png)";

                }

                if ($width > 600 || $height > 700) {
                    $errorMessages[] = "La imagen no puede superar 600x700 píxeles";
                }

                $destinationC = "../assets/imagenes/" . basename($real_name);
                $query = $con->prepare("SELECT nombre FROM productos WHERE imagen = :imagen");
                $query->execute([":imagen" => $destinationC]);
                if ($row = $query->fetch()) {
                    $errorMessages[] = "Ya existe una imagen con ese nombre en la base de datos";
                }
            }
        }

        // Execute update if no errors
        if (empty($errorMessages)) {
            $tmpImage = $_FILES["imagen"]["tmp_name"];
            $imgName = $_FILES["imagen"]["name"];
            $destination = "../assets/imagenes/" . $imgName;

            $sql = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, id_categoria = :categoria";
            $params = [
                ":nombre"      => $name,
                ":descripcion" => $description,
                ":precio"      => $price,
                ":categoria"   => $categoryId,
                ":id"          => $id
            ];

            if ($newImageUploaded && empty($errorMessages)) {
                $oldQuery = $con->prepare("SELECT imagen FROM productos WHERE id = :id");
                $oldQuery->execute([":id" => $id]);
                if ($oldRow = $oldQuery->fetch()) {
                    $oldImage = $oldRow["imagen"];
                    if (!empty($oldImage) && file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                if (move_uploaded_file($tmpImage, $destination)) {
                    $sql .= ", imagen = :imagen";
                    $params[":imagen"] = $destination;
                } else {
                    $errorMessages[] = "Se ha detectado un error inesperado al subir la imagen, intentelo de nuevo.";
                }
            }

            $sql .= " WHERE id = :id";

            $updateQuery = $con->prepare($sql);
            $updateQuery->execute($params);

            if ($updateQuery->rowCount() > 0) {
                header("Location: productoConsulta.php?nameE=$name");
                exit;
            } else {
                $errorMessages[] = "No se ha podido actualizar el producto en la base de datos.";
            }
        } 
    }

    // Retrieve product data for form
    $query = $con->prepare("SELECT id, nombre, descripcion, precio, id_categoria, imagen FROM productos WHERE id = :id");
    $query->execute([":id" => $id]);
    if ($row = $query->fetch()) {
        $nameM = $row["nombre"];
        $descriptionM = $row["descripcion"];
        $priceM = $row["precio"];
        $categoryM = $row["id_categoria"];
        $imageM = $row["imagen"];
    } else {
        $errorMessages[] = "No se ha encontrado el producto con el ID proporcionado.";
    }

    // Get categories for dropdown
    $catQuery = $con->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
    $categories = $catQuery->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar producto</title>
    <style>.error{border: solid 2px #FF0000;}</style>
</head>
<body>

    <h1>Formulario de edición de producto</h1>
    <p>Todos los campos son obligatorios:</p>

    <div id="errores" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <?php if (empty($errorMessages) || isset($_POST["enviar"])): ?>
    <form name="fEdicion" id="fEdicion" method="post" action="productoEditar.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <fieldset>
            <legend>Datos del producto:</legend>
            Nombre: <input type="text" name="nombre" size="50" maxlength="100" value="<?= htmlspecialchars($nameM) ?>">
            <br><br>

            Descripción: <input type="text" name="descripcion" size="80" maxlength="255" value="<?= htmlspecialchars($descriptionM) ?>">
            <br><br>

            Precio: <input type="text" name="precio" size="10" maxlength="10" value="<?= htmlspecialchars($priceM) ?>">
            <br><br>

            Categoría: 
            <select name="categoriaId" id="categoriaId">
                <option value="">-- Seleccione una categoría --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['id']) ?>" 
                        <?= ($cat['id'] == $categoryM) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            Imagen actual: 
            <?php if (!empty($imageM)): ?>
                <img src="<?= htmlspecialchars($imageM) ?>" width="100" height="200"><br>
            <?php endif; ?>
            Nueva imagen: <input type="file" name="imagen" accept=".jpg, .jpeg, .png">
        </fieldset>

        <br>
        <a href="productoConsulta.php" 
           style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Guardar cambios" name="enviar">
    </form>
    <?php endif; ?>

    <script type="text/javascript" src="productosValidacionForm.js"></script>
</body>
</html>

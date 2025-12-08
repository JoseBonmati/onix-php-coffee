<?php 
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Store error messages
    $errorMessages = [];

    if (isset($_POST["enviar"])) {
        $name = trim($_POST["nombre"] ?? "");
        $categoryId = trim($_POST["categoriaId"] ?? "");
        $description = trim($_POST["descripcion"] ?? "");
        $price = trim($_POST["precio"] ?? "");

        // Validations
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        if ($categoryId === "") {
            $errorMessages[] = "Debe seleccionar una categoría.";
        }

        if ($description === "") {
            $errorMessages[] = "El campo Descripción no puede estar vacío.";
        }

        if ($price === "") {
            $errorMessages[] = "El campo Precio no puede estar vacío.";
        } else if (!preg_match("/^[0-9]*\.?[0-9]+$/", $price)) {
            $errorMessages[] = "El formato introducido no es correcto, formato correcto [Números y/o números decimales]";
        }

        // Image handling
        if (empty($_FILES["imagen"]["tmp_name"])) {
            $errorMessages[] = "Debe subir una imagen";
        } else {
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
                    header("Location: productoConsulta.php?nameN=$name");
                } else {
                    $errorMessages[] = "Ha ocurrido un error con la base de datos.";
                }
            } else {
                $errorMessages[] = "Se ha detectado un error inesperado al subir la imagen, intentelo de nuevo.";
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
    <style> .error{border: solid 2px #FF0000;} </style>
</head>
<body>

    <h1>Formulario de creación de productos</h1>
    <p>Rellene los siguientes datos:</p>

    <div id="errores" style="color:red">
        <?php 
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <form name="fCreate" id="fCreate" method="post" action="productoCrear.php" enctype="multipart/form-data">

        <fieldset>
            <legend>Datos del producto:</legend>
            Nombre: <input type="text" name="nombre" id="nombre" size="50" maxlength="100" value="<?php if(isset($_POST['nombre'])) echo htmlspecialchars($_POST['nombre']); ?>">
            <br><br>

            Descripción: <input type="text" name="descripcion" id="descripcion" size="80" maxlength="255" value="<?php if(isset($_POST['descripcion'])) echo htmlspecialchars($_POST['descripcion']); ?>">
            <br><br>

            Precio: <input type="text" name="precio" id="precio" size="10" maxlength="10" value="<?php if(isset($_POST['precio'])) echo htmlspecialchars($_POST['precio']); ?>">
            <br><br>

            Categoría: 
            <select name="categoriaId" id="categoriaId">
                <option value="">-- Seleccione una categoría --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['id']) ?>" 
                        <?= (isset($_POST['categoriaId']) && $_POST['categoriaId'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            Imagen: <input type="file" name="imagen" id="imagen" accept=".jpg, .jpeg, .png">
        </fieldset>

        <br>
        <a href="productoConsulta.php" 
           style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Enviar" name="enviar"/>
    </form>

    <script type="text/javascript" src="productosValidacionForm.js"></script> 
</body>
</html>

<?php 
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Store error messages
    $errorMessages = [];

    if (isset($_POST["enviar"])) {
        $email = trim($_POST["email"] ?? "");
        $password = trim($_POST["contrasenya"] ?? "");
        $name = trim($_POST["nombre"] ?? "");
        $phone = trim($_POST["telefono"] ?? "");

        // Validate email
        if ($email === "") {
            $errorMessages[] = "El campo Email no puede estar vacío.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessages[] = "Formato de email no válido.";
        }

        // Validate password
        if ($password === "") {
            $errorMessages[] = "El campo Contraseña no puede estar vacío.";
        } elseif (strlen($password) < 8) {
            $errorMessages[] = "La contraseña debe tener al menos 8 caracteres.";
        }

        // Validate name
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }

        // Validate phone
        if ($phone === "") {
            $errorMessages[] = "El campo Teléfono no puede estar vacío.";
        } elseif (!preg_match("/^[0-9]{9}$/", $phone)) {
            $errorMessages[] = "El teléfono debe tener exactamente 9 dígitos numéricos.";
        }

        // Check if email already exists
        if (empty($errorMessages)) {
            $check = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
            $check->execute([":email" => $email]);
            if ($check->fetchColumn() > 0) {
                $errorMessages[] = "El email ya está registrado, use otro.";
            }
        }

        // Insert new user if no errors
        if (empty($errorMessages)) {
            $stmt = $con->prepare("INSERT INTO usuarios (nombre, email, contrasenya, telefono) 
                                   VALUES (:nombre, :email, :contrasenya, :telefono)");
            $stmt->execute([
                ":nombre" => $name,
                ":email" => $email,
                ":contrasenya" => password_hash($password, PASSWORD_DEFAULT),
                ":telefono" => $phone,
            ]);

            if ($stmt->rowCount() > 0) {
                if ($_SESSION["rol"] === "administrador") {
                    header("Location: usuarioConsulta.php?nameN=$name&emailN=$email");
                } else {
                    header("Location: login.php?nameN=$name&emailN=$email");
                }
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
    <title>Nuevos usuarios</title>
    <style> .error{border: solid 2px #FF0000;} </style>
</head>
<body>

    <h1>Formulario de creación de usuarios</h1>
    <p>Rellene los siguientes datos:</p>

    <div id="errores" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <form name="fCreacion" id="fCreacion" method="post" action="usuarioCrear.php">

        <fieldset>
            <legend>Datos de sesión:</legend>
            Email: <input type="text" name="email" id="email" size="50" maxlength="50" value="<?php if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>">
            <br><br>

            Contraseña: <input type="password" name="contrasenya" id="contrasenya" size="30" maxlength="30">
            <br><br>
        </fieldset>

        <fieldset>
            <legend>Datos personales:</legend>
            Nombre: <input type="text" name="nombre" id="nombre" size="50" maxlength="50" value="<?php if(isset($_POST['nombre'])) echo htmlspecialchars($_POST['nombre']); ?>">
            <br><br>

            Teléfono: <input type="text" name="telefono" id="telefono" size="9" maxlength="9" value="<?php if(isset($_POST['telefono'])) echo htmlspecialchars($_POST['telefono']); ?>">
        </fieldset>

        <br>
        <a href="<?= (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') ? 'usuarioConsulta.php' : 'login.php' ?>" 
           style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Enviar" name="enviar"/>
    </form>

    <script type="text/javascript" src="usuariosValidacionForm.js"></script> 
</body>
</html>

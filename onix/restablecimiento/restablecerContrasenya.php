<?php
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    
    // Store error messages
    $errorMessages = [];

    if (isset($_POST["enviar"])) {
        $email = trim($_POST["email"] ?? "");
        $password = trim($_POST["contrasenya"] ?? "");

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

        // Process reset if no errors
        if (empty($errorMessages)) {
            $stmt = $con->prepare("SELECT nombre, estado FROM usuarios WHERE email = :email");
            $stmt->execute([":email" => $email]);

            if ($data = $stmt->fetch()) {
                if ($data["estado"] !== "activo") {
                    $errorMessages[] = "El usuario está inactivo. Contacte con el administrador.";
                } else {
                    $name = $data["nombre"];
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    $query = $con->prepare("UPDATE usuarios SET contrasenya = :contrasenya WHERE email = :email");
                    $query->execute([
                        ":contrasenya" => $passwordHash,
                        ":email" => $email
                    ]);

                    if ($query->rowCount() > 0) {
                        header("Location: ../usuarios/login.php?nombreR=$name");
                        exit;
                    } else {
                        $errorMessages[] = "No se ha podido actualizar la contraseña. Inténtelo de nuevo.";
                    }
                }
            } else {
                $errorMessages[] = "El email introducido no es correcto, o no existe un usuario con dichos datos.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de contraseñas</title>
    <style>.error{border: solid 2px #FF0000;}</style>
</head>
<body>

    <h1>Restablecimiento de contraseñas</h1>

    <div id="errores" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <form name="fRestablecimiento" id="fRestablecimiento" method="post" action="restablecerContrasenya.php">
        <p>Para restablecer su contraseña, introduzca su correo electrónico, y a continuación escriba la nueva contraseña deseada.</p>

        Email: <input type="text" name="email" id="email" size="50" maxlength="50" value="<?php if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>">
        <br><br>

        Contraseña: <input type="password" name="contrasenya" id="contrasenya" size="30" maxlength="30">
        <br><br>

        <a href="../usuarios/login.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Restablecer contraseña" name="enviar">
    </form>

    <script type="text/javascript" src="../usuarios/usuariosValidacionForm.js"></script>
</body>
</html>

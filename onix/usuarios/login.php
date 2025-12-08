<?php
    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Store error messages
    $errorMessages = [];

    // Handle login form submission
    if (isset($_POST["iniciarS"])) {
        $email = trim($_POST["email"] ?? "");
        $password = trim($_POST["contrasenya"] ?? "");

        if ($email === "" || $password === "") {
            $errorMessages[] = "Los campos email y contraseña no pueden estar vacíos.";
        } else {
            $stmt = $con->prepare("SELECT id, nombre, contrasenya, rol, estado FROM usuarios WHERE email = :email");
            $stmt->execute([":email" => $email]);

            if ($data = $stmt->fetch()) {
                if ($data["estado"] !== "activo") {
                    $errorMessages[] = "El usuario está inactivo. Contacte con el administrador.";
                } elseif (password_verify($password, $data["contrasenya"])) {
                    session_regenerate_id(true);
                    $_SESSION["id"] = $data["id"];
                    $_SESSION["email"] = $email;
                    $_SESSION["rol"] = $data["rol"];
                    header("Location: ../index.php");
                    exit;
                } else {
                    $errorMessages[] = "El email o la contraseña son incorrectos.";
                }
            } else {
                $errorMessages[] = "El email o la contraseña son incorrectos.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <style>.error{border: solid 2px #FF0000;}</style>
</head>
<body>
    <h1>Bienvenido/a</h1>

    <?php 
        // Success messages for user actions
        if (isset($_GET["nameN"]) && isset($_GET["emailN"])) {
            $nameN = htmlspecialchars($_GET["nameN"]);
            $emailN = htmlspecialchars($_GET["emailN"]);
            echo "<p style='color:green'><b>El usuario $nameN con email $emailN se ha creado correctamente.</b></p>";
        }
        if (isset($_GET["nameR"])) {
            $nameR = htmlspecialchars($_GET["nameR"]);
            echo "<p style='color:green'><b>El usuario $nameR ha restablecido su contraseña correctamente.</b></p>";
        }
        if (isset($_GET["acceso"])) {
            echo "<p style='color:red'><b>Ha intentado acceder sin un usuario válido, inicie sesión para acceder.</b></p>";
        }
    ?>

    <div id="errors" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <p>Inicie sesión o regístrese si no dispone de una cuenta de usuario:</p>

    <form name="loginForm" id="loginForm" method="post" action="login.php">
        <fieldset>
            <legend>Iniciar sesión:</legend>
            Email: <input type="text" name="email" id="email" size="50" maxlength="50" value="<?php if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>">
            <br><br>
            Contraseña: <input type="password" name="contrasenya" id="contrasenya" size="30" maxlength="30">
            <br><br>
            <input type="submit" value="Iniciar sesión" name="iniciarS"/>
            <a href="../index.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        </fieldset>
        <fieldset>
            <legend>¿Problemas?</legend> 
            A continuación tiene la opción de registrarse si no posee una cuenta de usuario, o restablecer la contraseña en caso de que la haya olvidado.
            <br><br>
            <a href="usuarioCrear.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Registrarse</a>
            <a href="../restablecimiento/restablecerContrasenya.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Restablecer contraseña</a>
        </fieldset>
    </form>

    <script type="text/javascript" src="usuariosValidacionForm.js></script>
</body>
</html>


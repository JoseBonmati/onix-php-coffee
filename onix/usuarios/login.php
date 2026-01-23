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
                    $errorMessages[] = "El usuario está inactivo. Contacte con el correo@cafeteriaonix.com.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
    <title>Login Onix</title>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg">
        <div class="p-4 onix-card">
            <h2 class="text-center mb-4 fw-bold">Bienvenido/a</h2>
            <div class="mb-3">
                <?php
                    // Success messages for user actions
                    if (isset($_GET["nameN"]) && isset($_GET["emailN"])) {
                        $nameN = htmlspecialchars($_GET["nameN"]);
                        $emailN = htmlspecialchars($_GET["emailN"]);
                        echo "<p class='alert alert-success'>El usuario <b>$nameN</b> con email <b>$emailN</b> se ha creado correctamente.</p>";
                    }
                    if (isset($_GET["nameR"])) {
                        $nameR = htmlspecialchars($_GET["nameR"]);
                        echo "<p class='alert alert-success'>El usuario <b>$nameR<b> ha restablecido su contraseña correctamente.</p>";
                    }
                    if (isset($_GET["acceso"])) {
                        echo "<p class='alert alert-danger'>Ha intentado acceder sin un usuario válido, inicie sesión para acceder.</p>";
                    }
                    if (isset($_GET["cuentaDesactivada"])) {
                        echo "<p class='alert alert-warning'>Tu cuenta ha sido desactivada correctamente.</p>";
                    }
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client errors -->
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>
            
            <form name="fLogin" id="fLogin" method="post" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control onix-input" name="email" id="email" maxlength="50" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="contrasenya" class="form-label">Contraseña</label>
                    <input type="password" class="form-control onix-input" name="contrasenya" id="contrasenya" maxlength="30">
                </div>
                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="iniciarS">Iniciar sesión</button>
                    <hr class="onix-divider">
                    <a href="usuarioCrear.php" class="btn fw-semibold btn-outline-onix">Registrarse</a>
                </div>
                <div class="text-center mt-3">
                    <p class="mb-1">¿Problemas para iniciar sesión?</p>
                    <a href="restablecerContrasenya.php" class="fw-semibold text-onix">Restablecer contraseña</a>
                </div>
                <div class="text-center text-lg-start mt-3">
                    <a href="../index.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script type="text/javascript" src="usuariosValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


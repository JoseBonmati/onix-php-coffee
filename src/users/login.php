<?php

    // Session Management
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    // Store error messages
    $errorMessages = [];

    // Handle login form submission
    if (isset($_POST["login_submit"])) {
        $email = trim($_POST["email"] ?? "");
        $password = trim($_POST["password"] ?? "");

        if ($email === "" || $password === "") {
            $errorMessages[] = "Los campos email y contraseña no pueden estar vacíos.";
        } else {
            // Fetch user from DB
            $stmt = $db->prepare("SELECT id, nombre AS name, contrasenya AS password, rol AS role, estado AS status FROM usuarios WHERE email = :email");
            $stmt->execute([":email" => $email]);

            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($user["status"] !== "activo") {
                    $errorMessages[] = "El usuario está inactivo. Contacte con el correo@cafeteriaonix.com.";
                } elseif (password_verify($password, $user["password"])) {
                    // Prevent session fixation
                    session_regenerate_id(true);
                    
                    $_SESSION["id"] = $user["id"];
                    $_SESSION["email"] = $email;
                    $_SESSION["name"] = $user["name"];
                    
                    // Role mapping: DB (Spanish) to App Logic (English)
                    $_SESSION["role"] = ($user["role"] === "administrador") ? "admin" : "user";
                    
                    header("Location: /index.php");
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
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
    <title>Login Onix</title>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg">
        <div class="p-4 onix-card">
            <h2 class="text-center mb-4 fw-bold">Bienvenido/a</h2>
            <div class="mb-3">
                <?php
                    // Success messages for user actions
                    if (isset($_GET["new_name"]) && isset($_GET["new_email"])) {
                        $newName = htmlspecialchars($_GET["new_name"]);
                        $newEmail = htmlspecialchars($_GET["new_email"]);
                        echo "<p class='alert alert-success'>El usuario <b>$newName</b> con email <b>$newEmail</b> se ha creado correctamente.</p>";
                    }
                    if (isset($_GET["reset_name"])) {
                        $resetName = htmlspecialchars($_GET["reset_name"]);
                        echo "<p class='alert alert-success'>El usuario <b>$resetName</b> ha restablecido su contraseña correctamente.</p>";
                    }
                    if (isset($_GET["error"]) && $_GET["error"] === "access_denied") {
                        echo "<p class='alert alert-danger'>Ha intentado acceder sin un usuario válido, inicie sesión para acceder.</p>";
                    }
                    if (isset($_GET["account_deactivated"])) {
                        echo "<p class='alert alert-warning'>Tu cuenta ha sido desactivada correctamente.</p>";
                    }
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client errors -->
            <div id="errors" class="mb-3 text-danger fw-semibold"></div>
            
            <form name="login_form" id="login_form" method="post" action="/users/login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control onix-input" name="email" id="email" maxlength="50" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control onix-input" name="password" id="password" maxlength="30">
                </div>
                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="login_submit">Iniciar sesión</button>
                    <hr class="onix-divider">
                    <a href="/users/user_create.php" class="btn fw-semibold btn-outline-onix">Registrarse</a>
                </div>
                <div class="text-center mt-3">
                    <p class="mb-1">¿Problemas para iniciar sesión?</p>
                    <a href="/users/user_reset_password.php" class="fw-semibold text-onix">Restablecer contraseña</a>
                </div>
                <div class="text-center text-lg-start mt-3">
                    <a href="/index.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/users/users_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();
    
    $errorMessages = [];

    if (isset($_POST["reset_submit"])) {
        $email = trim($_POST["email"] ?? "");
        $password = trim($_POST["password"] ?? "");

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
            $stmt = $db->prepare("SELECT nombre AS name, estado AS status FROM usuarios WHERE email = :email");
            $stmt->execute([":email" => $email]);

            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($user["status"] !== "activo") {
                    $errorMessages[] = "El usuario está inactivo. Contacte con el administrador.";
                } else {
                    $name = $user["name"];
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    $updateStmt = $db->prepare("UPDATE usuarios SET contrasenya = :password WHERE email = :email");
                    $updateStmt->execute([
                        ":password" => $passwordHash,
                        ":email" => $email
                    ]);

                    if ($updateStmt->rowCount() >= 0) {
                        header("Location: /users/login.php?reset_name=" . urlencode($name));
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
    <title>Restablecer contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg">
        <div class="p-4 onix-card">
            <h2 class="text-center mb-4 fw-bold">Restablecer contraseña</h2>
            
            <div class="mb-3">
                <?php
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client errors -->
            <div id="errors" class="mb-3 text-danger fw-semibold"></div>
            
            <form name="reset_form" id="reset_form" method="post" action="/users/user_reset_password.php">
                <p class="mb-3 text-center fw-semibold">Introduce tu correo electrónico y la nueva contraseña que deseas establecer.</p>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control onix-input" name="email" id="email" maxlength="50" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <input type="password" class="form-control onix-input" name="password" id="password" maxlength="30">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="reset_submit">Restablecer contraseña</button>
                    <hr class="onix-divider">
                    <a href="/users/login.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/users/users_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
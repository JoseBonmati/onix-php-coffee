<?php 

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    $errorMessages = [];

    if (isset($_POST["register_submit"])) {
        $email = trim($_POST["email"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");

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
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
            $checkStmt->execute([":email" => $email]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $errorMessages[] = "El email ya está registrado, use otro.";
            }
        }

        // Insert new user if no errors
        if (empty($errorMessages)) {
            $stmt = $db->prepare("INSERT INTO usuarios (nombre, email, contrasenya, telefono, rol, estado) 
                                  VALUES (:name, :email, :password, :phone, 'usuario', 'activo')");
            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => password_hash($password, PASSWORD_DEFAULT),
                ":phone" => $phone
            ]);

            if ($stmt->rowCount() > 0) {
                if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
                    header("Location: /users/user_list.php?created_name=" . urlencode($name) . "&created_email=" . urlencode($email));
                    exit;
                }

                $newUserId = $db->lastInsertId();

                $_SESSION["id"] = $newUserId;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = "user";
                $_SESSION["name"] = $name;

                header("Location: /index.php?new_name=" . urlencode($name) . "&new_email=" . urlencode($email));
                exit;
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
    <title>Nuevo usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center onix-bg">
        <div class="p-4 onix-card">
            <h2 class="text-center mb-4 fw-bold">Crear usuario</h2>

            <!-- Server-side errors -->
            <div class="mb-3">
                <?php
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client-side errors -->
            <div id="errors" class="mb-3 text-danger fw-semibold"></div>
            
            <form name="register_form" id="register_form" method="post" action="/users/user_create.php">
                <p class="mb-3 text-center fw-semibold">Rellena los siguientes datos para crear un nuevo usuario.</p>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control onix-input" name="email" id="email" maxlength="50" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control onix-input" name="password" id="password" maxlength="30">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control onix-input" name="name" id="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control onix-input" name="phone" id="phone" maxlength="9" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="register_submit">Crear usuario</button>
                    <hr class="onix-divider">
                    <a href="<?= (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? '/users/user_list.php' : '/users/login.php' ?>" 
                       class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="/users/users_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
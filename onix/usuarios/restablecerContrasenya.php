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
                        header("Location: login.php?nombreR=" . urlencode($name));
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
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
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
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>
            
            <form name="fRestablecimiento" id="fRestablecimiento" method="post" action="restablecerContrasenya.php">
                <p class="mb-3 text-center fw-semibold">Introduce tu correo electrónico y la nueva contraseña que deseas establecer.</p>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control onix-input" name="email" id="email" maxlength="50" value="<?php if(isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>">
                </div>

                <div class="mb-3">
                    <label for="contrasenya" class="form-label">Nueva contraseña</label>
                    <input type="password" class="form-control onix-input" name="contrasenya" id="contrasenya" maxlength="30">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="enviar">Restablecer contraseña</button>
                    <hr class="onix-divider">
                    <a href="login.php" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="usuariosValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

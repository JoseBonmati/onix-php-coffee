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
                if (isset($_SESSION["rol"]) && $_SESSION["rol"] === "administrador") {
                    header("Location: usuarioConsulta.php?nombreN=" . urlencode($name) . "&emailN=" . urlencode($email));
                    exit;
                }

                // If you are a regular user registering, you will be logged in automatically
                $idNewUser = $con->lastInsertId();

                $_SESSION["id"] = $idNewUser;
                $_SESSION["email"] = $email;
                $_SESSION["rol"] = "usuario";
                $_SESSION["nombre"] = $nombre;

                header("Location: ../index.php?nombreN=" . urlencode($nombre) . "&emailN=" . urlencode($email));
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
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
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
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>
            
            <form name="fCreacion" id="fCreacion" method="post" action="usuarioCrear.php">
                <p class="mb-3 text-center fw-semibold">Rellena los siguientes datos para crear un nuevo usuario.</p>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="text" class="form-control onix-input" name="email" id="email" maxlength="50" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="contrasenya" class="form-label">Contraseña</label>
                    <input type="password" class="form-control onix-input" name="contrasenya" id="contrasenya" maxlength="30">
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control onix-input" name="nombre" id="nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control onix-input" name="telefono" id="telefono" maxlength="9" value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '' ?>">
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn fw-semibold btn-onix" name="enviar">Crear usuario</button>
                    <hr class="onix-divider">
                    <a href="<?= (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') ? 'usuarioConsulta.php' : 'login.php' ?>" 
                       class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="usuariosValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


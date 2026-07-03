<?php

    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Store error messages
    $errorMessages = [];

    $returnTo = $_GET["from"] ?? $_POST["from"] ?? (isset($_GET["onlyMine"]) ? "onlyMine" : null);

    $role = $_SESSION["rol"];

    // Obtain ID of user to edit
    if ($role === "administrador") {
        if (isset($_POST["enviar"]) || isset($_POST["cambiarRol"])) {
            $id = (int) ($_POST["id"] ?? 0);
        } else {
            $id = (int) ($_GET["id"] ?? 0);
        }
    } else {
        $id = $_SESSION["id"];
    }

    // Restrict access: only admin or the same user
    if ($_SESSION["rol"] !== "administrador" && $_SESSION["id"] !== $id) {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Handle form submission (update data and optionally role/status)
    if (isset($_POST["enviar"]) || isset($_POST["cambiarRol"])) {
        $name = trim($_POST["nombre"] ?? "");
        $phone = trim($_POST["telefono"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["contrasenya"] ?? "";

        // Validations
        if ($name === "") {
            $errorMessages[] = "El campo Nombre no puede estar vacío.";
        }
        if ($phone === "" || !preg_match("/^[0-9]{9}$/", $phone)) {
            $errorMessages[] = "El teléfono debe tener exactamente 9 dígitos numéricos.";
        }
        if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessages[] = "Formato de email no válido.";
        }

        // Check if email already exists for another user
        if (empty($errorMessages)) {
            $check = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :id");
            $check->execute([
                ":email" => $email,
                ":id" => $id
            ]);
            if ($check->fetchColumn() > 0) {
                $errorMessages[] = "El email ya está registrado por otro usuario, use otro.";
            }
        }

        // Base SQL update
        $sql = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, email = :email";
        $params = [
            ":nombre" => $name,
            ":telefono" => $phone,
            ":email" => $email,
            ":id" => $id
        ];

        // Optional password update
        if ($password !== "") {
            if (strlen($password) < 8) {
                $errorMessages[] = "La contraseña debe tener al menos 8 caracteres.";
            } else {
                $sql .= ", contrasenya = :contrasenya";
                $params[":contrasenya"] = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        // Admin-only role and status update
        if ($role === "administrador") {
            if (isset($_POST["cambiarRol"])) {
                $role = $_POST["cambiarRol"];
            } else {
                $roleQuery = $con->prepare("SELECT rol FROM usuarios WHERE id = :id");
                $roleQuery->execute([":id" => $id]);
                $role = $roleQuery->fetchColumn();
            }
            $sql .= ", rol = :rol"; 
            $params[":rol"] = $role;
        }

        $sql .= " WHERE id = :id";

        // Execute update if no errors
        if (empty($errorMessages)) {
            $updateQuery = $con->prepare($sql);
            $updateQuery->execute($params);

            if ($_SESSION["id"] == $id) {
                $_SESSION["nombre"] = $name;
                $_SESSION["email"] = $email;
            }

            if ($updateQuery->rowCount() > 0) {

                // If you are an administrator and are editing your user, return to your profile
                if ($_SESSION["rol"] === "administrador" && $returnTo === "onlyMine") {
                    header("Location: perfil.php?nameE=" . urlencode($name) . "&emailE=" . urlencode($email));
                    exit;
    
                // If you are an administrator editing another user, return to the query
                } elseif ($_SESSION["rol"] === "administrador") {
                    header("Location: usuarioConsulta.php?nameE=" . urlencode($name) . "&emailE=" . urlencode($email));
                    exit;

                } else {
                    // If you are a regular user, you will always return to your profile
                    header("Location: perfil.php?nameE=" . urlencode($name) . "&emailE=" . urlencode($email));
                    exit;
                }

            } else {
                $errorMessages[] = "No se ha podido actualizar el usuario.";
            }
        }
    }

    // Retrieve user data for form
    $query = $con->prepare("SELECT id, nombre, email, telefono, rol, estado FROM usuarios WHERE id = :id");
    $query->execute([":id" => $id]);
    if ($row = $query->fetch()) {
        $nameM   = $row["nombre"];
        $emailM  = $row["email"];
        $phoneM  = $row["telefono"];
        $roleM   = $row["rol"];
        $statusM = $row["estado"];
    } else {
        $errorMessages[] = "No se ha encontrado el usuario con el ID proporcionado.";
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edición de usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="icon" type="image/x-icon" href="../assets/logos/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg">
        <div class="p-4 onix-card" style="max-width: 700px;">
            <h2 class="text-center mb-4 fw-bold">Editar usuario</h2>

            <!-- Server-side errors -->
            <div class="mb-3">
                <?php
                    if (isset($_GET["estadoCambiado"])) {
                        echo "<p class='alert alert-success mb-0'>Estado del usuario cambiado.</p>";
                    }
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <!-- Client-side errors -->
            <div id="errores" class="mb-3 text-danger fw-semibold"></div>

            <form name="fEdicion" id="fEdicion" method="post" action="usuarioEditar.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                <input type="hidden" name="from" value="<?= htmlspecialchars($returnTo) ?>">

                <p class="mb-4 text-center fw-semibold">Modifica los datos del usuario y guarda los cambios.</p>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico</label>
                    <input type="text" name="email" id="email" class="form-control onix-input" maxlength="50" value="<?= htmlspecialchars($emailM) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contraseña</label>
                    <input type="password" name="contrasenya" class="form-control onix-input" maxlength="30">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control onix-input" maxlength="30" value="<?= htmlspecialchars($nameM) ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control onix-input" maxlength="9" value="<?= htmlspecialchars($phoneM) ?>">
                </div>

                <?php if ($_SESSION["rol"] === "administrador" && $_SESSION["id"] != $id): ?>
                    <h5 class="fw-bold mt-4 mb-3">Gestión administrativa</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rol</label><br>
                        <?php if ($roleM === "usuario"): ?>
                            <button type="submit" name="cambiarRol" value="administrador" class="btn btn-outline-danger fw-semibold" 
                                onclick="return confirm('¿Convertir este usuario en administrador?');">
                                Convertir en Administrador
                            </button>
                        <?php else: ?>
                            <button type="submit" name="cambiarRol" value="usuario" class="btn btn-outline-danger fw-semibold"
                                onclick="return confirm('¿Quitar permisos de administrador?');">
                                Quitar de Administradores
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <?php if ($statusM === "activo"): ?>
                            <a href="usuarioDesactivar.php?id=<?= $id ?>&action=desactivar" class="btn btn-outline-warning fw-semibold"
                            onclick="return confirm('¿Seguro que quieres desactivar este usuario?');">Desactivar usuario</a>
                        <?php else: ?>
                            <a href="usuarioDesactivar.php?id=<?= $id ?>&action=activar" class="btn btn-outline-success fw-semibold"
                            onclick="return confirm('¿Seguro que quieres activar este usuario?');">Activar usuario</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3 mt-4">
                    <button type="submit" name="enviar" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <hr class="onix-divider">

                    <?php

                        // If you are a regular user, you will always return to your profile
                        if ($_SESSION["rol"] === "usuario") {
                            $return = "perfil.php";

                        // If you are an administrator and are editing your user, return to your profile
                        } elseif (isset($_GET["onlyMine"]) && $_GET["onlyMine"] == 1) {
                            $return = "perfil.php";

                        // If you are an administrator editing another user, return to the query
                        } else {
                            $return = "usuarioConsulta.php";
                        }
                        
                    ?>

                    <a href="<?= $return ?>" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <script src="usuariosValidacionForm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

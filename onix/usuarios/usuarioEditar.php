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

    // Get user ID from POST or GET
    if (isset($_POST["enviar"]) || isset($_POST["cambiarRol"])) {
        $id = (int) ($_POST["id"] ?? 0);
    } else {
        $id = (int) ($_GET["id"] ?? 0);
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
        if ($_SESSION["rol"] === "administrador") {
            if (isset($_POST["cambiarRol"])) {
                $role = $_POST["cambiarRol"];
            } else {
                $roleQuery = $con->prepare("SELECT rol FROM usuarios WHERE id = :id");
                $roleQuery->execute([":id" => $id]);
                $role = $roleQuery->fetchColumn();
            }
            $status = $_POST["estado"] ?? "activo";
            $sql .= ", rol = :rol, estado = :estado";
            $params[":rol"] = $role;
            $params[":estado"] = $status;
        }

        $sql .= " WHERE id = :id";

        // Execute update if no errors
        if (empty($errorMessages)) {
            $updateQuery = $con->prepare($sql);
            $updateQuery->execute($params);

            if ($updateQuery->rowCount() > 0) {
                if ($_SESSION["rol"] === "administrador") {
                    header("Location: usuarioConsulta.php?nameE=$name&emailE=$email");
                } else {
                    header("Location: perfil.php?nameE=$name&emailE=$email");
                }
                exit;
            } else {
                $errorMessages[] = "No se ha podido actualizar el usuario en la base de datos.";
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
    <title>Editar usuario</title>
    <style>.error{border: solid 2px #FF0000;}</style>
</head>
<body>

    <h1>Formulario de edición de usuario</h1>
    <p>Todos los campos son obligatorios (excepto la contraseña, que es opcional):</p>

    <div id="errores" style="color:red">
        <?php 
            // Display error messages if any
            if (!empty($errorMessages)) {
                echo "<b>" . implode("<br>", $errorMessages) . "</b>";
            }
        ?>
    </div>

    <?php if (empty($errorMessages) || isset($_POST["enviar"]) || isset($_POST["cambiarRol"])): ?>
    <form name="fEdicion" id="fEdicion" method="post" action="usuarioEditar.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <fieldset>
            <legend>Datos de sesión:</legend>
            Email: <input type="text" name="email" size="50" maxlength="50" value="<?= htmlspecialchars($emailM) ?>">
            <br><br>

            Contraseña: <input type="password" name="contrasenya" size="30" maxlength="30">
            <br><br>
        </fieldset>     

        <fieldset>
            <legend>Datos personales:</legend>        
            Nombre: <input type="text" name="nombre" size="30" maxlength="30" value="<?= htmlspecialchars($nameM) ?>">
            <br><br>

            Teléfono: <input type="text" name="telefono" size="9" maxlength="9" value="<?= htmlspecialchars($phoneM) ?>">
            <br><br>

            <?php if ($_SESSION["rol"] === "administrador"): ?>
                <fieldset>
                    <legend>Gestión de rol:</legend>
                    <?php if ($roleM === "usuario"): ?>
                        <button type="submit" name="cambiarRol" value="administrador"
                            onclick="return confirm('¿Está seguro de que quiere convertir este usuario en administrador?');">
                            Cambiar a Administrador
                        </button>
                    <?php else: ?>
                        <button type="submit" name="cambiarRol" value="usuario"
                            onclick="return confirm('¿Está seguro de que quiere quitar a este usuario de administradores?');">
                            Quitar de administradores
                        </button>
                    <?php endif; ?>
                </fieldset>

                <br>
                Estado: <select name="estado" required>
                    <?php 
                        $statuses = ["activo", "inactivo"];
                        foreach ($statuses as $status) {
                            $selected = ($status == $statusM) ? "selected" : "";
                            echo "<option value='$status' $selected>$status</option>";
                        }
                    ?>
                </select>
            <?php endif; ?>
        </fieldset>

        <br>
        <a href="<?= $_SESSION['rol']==='administrador' ? 'usuarioConsulta.php' : 'perfil.php' ?>" 
           style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Guardar cambios" name="enviar">
    </form>
    <?php endif; ?>

    <script type="text/javascript" src="usuariosValidacionForm.js"></script>
</body>
</html>

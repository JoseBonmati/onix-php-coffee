<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . "/../utils/Database.php";
    $db = Database::getConnection();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    $errorMessages = [];
    $returnTo = $_GET["from"] ?? $_POST["from"] ?? (isset($_GET["only_mine"]) ? "only_mine" : null);
    $currentRole = $_SESSION["role"] ?? "user";

    if ($currentRole === "admin") {
        if (isset($_POST["edit_submit"]) || isset($_POST["change_role"])) {
            $id = (int) ($_POST["id"] ?? 0);
        } else {
            $id = (int) ($_GET["id"] ?? 0);
        }
    } else {
        $id = $_SESSION["id"];
    }

    if ($currentRole !== "admin" && $_SESSION["id"] !== $id) {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    if (isset($_POST["edit_submit"]) || isset($_POST["change_role"])) {
        $name = trim($_POST["name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

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
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email AND id != :id");
            $checkStmt->execute([":email" => $email, ":id" => $id]);

            if ($checkStmt->fetchColumn() > 0) {
                $errorMessages[] = "El email ya está registrado por otro usuario.";
            }
        }

        $sql = "UPDATE usuarios SET nombre = :name, telefono = :phone, email = :email";
        $params = [
            ":name" => $name,
            ":phone" => $phone,
            ":email" => $email,
            ":id" => $id
        ];

        // Optional password update
        if ($password !== "") {
            if (strlen($password) < 8) {
                $errorMessages[] = "La contraseña debe tener al menos 8 caracteres.";
            } else {
                $sql .= ", contrasenya = :password";
                $params[":password"] = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        if ($currentRole === "admin") {
            if (isset($_POST["change_role"])) {
                $selectedRole = $_POST["change_role"];
            } else {
                $roleQuery = $db->prepare("SELECT rol FROM usuarios WHERE id = :id");
                $roleQuery->execute([":id" => $id]);
                $selectedRole = $roleQuery->fetchColumn();
            }
            $sql .= ", rol = :role";
            $params[":role"] = $selectedRole;
        }

        $sql .= " WHERE id = :id";

        // Execute update if no errors
        if (empty($errorMessages)) {
            $updateQuery = $db->prepare($sql);
            $updateQuery->execute($params);

            if ($_SESSION["id"] == $id) {
                $_SESSION["name"] = $name;
                $_SESSION["email"] = $email;
            }

            if ($updateQuery->rowCount() >= 0) {
                if ($currentRole === "admin" && $returnTo === "only_mine") {
                    header("Location: /users/profile.php?edited_name=" . urlencode($name) . "&edited_email=" . urlencode($email));
                    exit;
                } elseif ($currentRole === "admin") {
                    header("Location: /users/user_list.php?updated_name=" . urlencode($name) . "&updated_email=" . urlencode($email));
                    exit;
                } else {
                    header("Location: /users/profile.php?edited_name=" . urlencode($name) . "&edited_email=" . urlencode($email));
                    exit;
                }
            } else {
                $errorMessages[] = "No se ha podido actualizar el usuario.";
            }
        }
    }

    $query = $db->prepare("SELECT id, nombre AS name, email, telefono AS phone, rol AS role, estado AS status FROM usuarios WHERE id = :id");
    $query->execute([":id" => $id]);

    if ($user = $query->fetch(PDO::FETCH_ASSOC)) {
        $fetchedName = $user["name"];
        $fetchedEmail = $user["email"];
        $fetchedPhone = $user["phone"];
        $fetchedRole = $user["role"];
        $fetchedStatus = $user["status"];
    } else {
        $errorMessages[] = "No se ha encontrado el usuario.";
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
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/assets/brand/onix-favicon.ico"/>
</head>
<body>
    <div class="d-flex justify-content-center align-items-start onix-bg">
        <div class="p-4 onix-card" style="max-width: 700px;">
            <h2 class="text-center mb-4 fw-bold">Editar usuario</h2>

            <!-- Server-side errors -->
            <div class="mb-3">
                <?php
                    if (isset($_GET["status_changed"])) {
                        echo "<p class='alert alert-success mb-0'>Estado del usuario cambiado.</p>";
                    }
                    if (!empty($errorMessages)) {
                        echo "<p class='alert alert-danger mb-0'>" . implode("<br>", $errorMessages) . "</p>";
                    }
                ?>
            </div>

            <div id="errors" class="mb-3 text-danger fw-semibold"></div>

            <form name="edit_form" id="edit_form" method="post" action="/users/user_edit.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id) ?>">
                <input type="hidden" name="from" value="<?= htmlspecialchars((string)$returnTo) ?>">

                <p class="mb-4 text-center fw-semibold">Modifica los datos del usuario y guarda los cambios.</p>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                    <input type="text" name="email" id="email" class="form-control onix-input" maxlength="50" value="<?= htmlspecialchars($fetchedEmail ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control onix-input" maxlength="30">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control onix-input" maxlength="30" value="<?= htmlspecialchars($fetchedName ?? '') ?>">
                </div>

                <div class="mb-4">
                    <label for="phone" class="form-label fw-semibold">Teléfono</label>
                    <input type="text" name="phone" id="phone" class="form-control onix-input" maxlength="9" value="<?= htmlspecialchars($fetchedPhone ?? '') ?>">
                </div>

                <?php if ($currentRole === "admin" && $_SESSION["id"] != $id): ?>
                    <h5 class="fw-bold mt-4 mb-3">Gestión administrativa</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rol</label><br>
                        <?php if (isset($fetchedRole) && $fetchedRole === "usuario"): ?>
                            <button type="submit" name="change_role" value="administrador" class="btn btn-outline-danger fw-semibold" 
                                onclick="return confirm('¿Convertir este usuario en administrador?');">
                                Convertir en Administrador
                            </button>
                        <?php else: ?>
                            <button type="submit" name="change_role" value="usuario" class="btn btn-outline-danger fw-semibold"
                                onclick="return confirm('¿Quitar permisos de administrador?');">
                                Quitar de Administradores
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <?php if (isset($fetchedStatus) && $fetchedStatus === "activo"): ?>
                            <a href="/users/user_deactivate.php?id=<?= $id ?>&action=deactivate" class="btn btn-outline-warning fw-semibold"
                            onclick="return confirm('¿Seguro que quieres desactivar este usuario?');">Desactivar usuario</a>
                        <?php else: ?>
                            <a href="/users/user_deactivate.php?id=<?= $id ?>&action=activate" class="btn btn-outline-success fw-semibold"
                            onclick="return confirm('¿Seguro que quieres activar este usuario?');">Activar usuario</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3 mt-4">
                    <button type="submit" name="edit_submit" class="btn btn-onix fw-semibold">Guardar cambios</button>
                    <hr class="onix-divider">

                    <?php
                        if ($currentRole === "user") {
                            $returnUrl = "/users/profile.php";
                        } elseif (isset($_GET["only_mine"]) && $_GET["only_mine"] == 1) {
                            $returnUrl = "/users/profile.php";
                        } else {
                            $returnUrl = "/users/user_list.php";
                        }
                    ?>

                    <a href="<?= $returnUrl ?>" class="btn btn-outline-secondary">Volver</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="/users/users_validation_form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
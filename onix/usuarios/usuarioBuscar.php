<?php
    require_once "../utilidades/conectar_db.php";
    require_once "user.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can use this search
    if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    $sessionId = (int) $_SESSION["id"];

    // Render one result row with action links, blocking delete for own account
    function mostrarFila(User $user, $sessionId) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user->getId()) . "</td>";
        echo "<td>" . htmlspecialchars($user->getNombre()) . "</td>";
        echo "<td>" . htmlspecialchars($user->getEmail()) . "</td>";
        echo "<td>" . htmlspecialchars($user->getTelefono()) . "</td>";
        echo "<td>" . htmlspecialchars($user->getRol()) . "</td>";
        echo "<td>" . htmlspecialchars($user->getEstado()) . "</td>";

        $idEscaped = (int) $user->getId();

        if ($sessionId == $idEscaped) {
            echo "<td><a href='usuarioEditar.php?id={$idEscaped}'>📝</a></td>";
            echo "<td>🚫</td>";
        } else {
            echo "<td><a href='usuarioEditar.php?id={$idEscaped}'>📝</a></td>";
            echo "<td><a href='usuarioEliminar.php?id={$idEscaped}'>❌</a></td>";
        }
        echo "</tr>";
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar usuarios</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body>
    <h1>Búsqueda de usuarios</h1>
    <p>Introduce nombre o email para buscar:</p>

    <!-- Search form -->
    <form name="fBusqueda" method="post" action="usuarioBuscar.php">
        Nombre: <input type="text" name="nombreB" size="30" maxlength="50">
        <br><br>
        Email: <input type="text" name="emailB" size="30" maxlength="50">
        <br><br>
        <a href="usuarioConsulta.php" style="text-decoration:none; color:black; padding:5px; border:1px solid #000; background:#eee;">Volver</a>
        <input type="submit" value="Buscar" name="enviar"/>
    </form>

<?php
    // Handle search submission
    if (isset($_POST["enviar"])) {
        echo "<h2>Resultado de la búsqueda:</h2>";

        $nameQ = trim($_POST["nombreB"] ?? "");
        $emailQ = trim($_POST["emailB"] ?? "");

        if ($nameQ === "" && $emailQ === "") {
            echo "<p style='color:red'><b>Debes introducir al menos nombre o email para comenzar la búsqueda.</b></p>";
        } else {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Editar</th>
                    <th>Borrar</th>
            </tr>";

            // Prepare query based on provided field
            if ($nameQ !== "") {
                $query = $con->prepare("SELECT id, nombre AS name, email, contrasenya AS password, telefono AS phone, rol AS role, estado AS status 
                                        FROM usuarios WHERE nombre LIKE :nombre");
                $query->execute([":nombre" => "%" . $nameQ . "%"]);
            } elseif ($emailQ !== "") {
                $query = $con->prepare("SELECT id, nombre AS name, email, contrasenya AS password, telefono AS phone, rol AS role, estado AS status 
                                        FROM usuarios WHERE email LIKE :email");
                $query->execute([":email" => "%" . $emailQ . "%"]);
}

            // Map results to User objects
            $query->setFetchMode(PDO::FETCH_CLASS, "User");
            $users = $query->fetchAll();

            if ($users) {
                foreach ($users as $user) {
                    mostrarFila($user, $sessionId);
                }
            } else {
                echo "<p style='color:red'><b>No existe ningún usuario con esos datos.</b></p>";
            }

            echo "</table>";
        }
    }
?>
</body>
</html>

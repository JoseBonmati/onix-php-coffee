<?php
    require_once "../utilidades/conectar_db.php";
    require_once "user.php";
    $con = conectar();
    session_start();

    // Restrict access: only administrators can view this page
    if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "administrador") {
        header("Location: login.php?acceso=denegado");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../estilos.css">
    <title>Consulta de usuarios</title>
</head>
<body>
    <h1>Consultas de usuarios</h1>

    <?php
        // Success messages after CRUD actions
        if (isset($_GET["nameE"]) && isset($_GET["emailE"])) {
            $nameE = htmlspecialchars($_GET["nameE"]);
            $emailE = htmlspecialchars($_GET["emailE"]);
            echo "<p style='color:green'><b>El usuario $nameE con email $emailE ha sido modificado correctamente.</b></p>";
        }

        if (isset($_GET["nameN"]) && isset($_GET["emailN"])) {
            $nameN = htmlspecialchars($_GET["nameN"]);
            $emailN = htmlspecialchars($_GET["emailN"]);
            echo "<p style='color:green'><b>El usuario $nameN con email $emailN se ha creado correctamente.</b></p>";
        }

        if (isset($_GET["nameD"])) {
            $nameD = htmlspecialchars($_GET["nameD"]);
            echo "<p style='color:green'><b>El usuario $nameD se ha eliminado correctamente.</b></p>";
        }
    ?>

    <table>
        <tr>
            <!-- Table headers with sorting links -->
            <th><a href="usuarioConsulta.php?order=id&orderType=ASC">ID</a></th>
            <th><a href="usuarioConsulta.php?order=nombre&orderType=ASC">Nombre</a></th>
            <th><a href="usuarioConsulta.php?order=email&orderType=ASC">Email</a></th>
            <th><a href="usuarioConsulta.php?order=telefono&orderType=ASC">Teléfono</a></th>
            <th><a href="usuarioConsulta.php?order=rol&orderType=ASC">Rol</a></th>
            <th><a href="usuarioConsulta.php?order=estado&orderType=ASC">Estado</a></th>
            <th id="editar"><b>Editar</b></th>
            <th id="borrar"><b>Borrar</b></th>
        </tr>

        <?php
            // Pagination and sorting setup
            $page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
            $resultsPP = 5;

            $allowedColumns = ["id","nombre","email","telefono","rol","estado"];
            $order = isset($_GET["order"]) ? $_GET["order"] : "nombre";
            if (!in_array($order, $allowedColumns)) {
                $order = "nombre";
            }

            $orderType = isset($_GET["orderType"]) ? strtoupper($_GET["orderType"]) : "ASC";
            if (!in_array($orderType, ["ASC", "DESC"])) {
                $orderType = "ASC";
            }

            // Query users with pagination and sorting
            $users = obtenerUsuarios($con, $page, $resultsPP, $order, $orderType);

            // Render table rows
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user->getId()) . "</td>";
                echo "<td>" . htmlspecialchars($user->getNombre()) . "</td>";
                echo "<td>" . htmlspecialchars($user->getEmail()) . "</td>";
                echo "<td>" . htmlspecialchars($user->getTelefono()) . "</td>";
                echo "<td>" . htmlspecialchars($user->getRol()) . "</td>";
                echo "<td>" . htmlspecialchars($user->getEstado()) . "</td>";

                // Prevent editing/deleting own account
                if ($_SESSION["id"] == $user->getId()) {
                    echo "<td> 🚫 </td>";
                    echo "<td> 🚫 </td>";
                } else {
                    echo "<td><a href='usuarioEditar.php?id=" . $user->getId() . "'> 📝 </a></td>";
                    echo "<td><a href='usuarioEliminar.php?id=" . $user->getId() . "'> ❌ </a></td>";
                }
                echo "</tr>";
            }
        ?>
    </table>

    <br><br>

    <?php
        // Pagination controls
        $query = $con->prepare("SELECT count(*) AS total FROM usuarios");
        $query->execute();
        $row = $query->fetch();
        $totalUsers = $row["total"];

        $totalPages = ceil($totalUsers / $resultsPP);

        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                echo "<button style='margin: 5px'><a style='text-decoration:none; color:black;'>$i</a></button>";
            } else {
                echo "<button style='margin: 5px'><a style='text-decoration:none; color:black;' href='usuarioConsulta.php?page=$i&order=$order&orderType=$orderType'>$i</a></button>";
            }
        }

        echo "<br><br>";
        echo '<a href="usuarioConsulta.php?order='.$order.'&orderType=ASC" style="margin: 5px"><button>Orden ascendente</button></a>';
        echo '<a href="usuarioConsulta.php?order='.$order.'&orderType=DESC" style="margin: 5px"><button>Orden descendente</button></a>';
        echo "<br><br>";
    ?>

    <a href="../utilidades/panelAdministrador.php" style="margin: 5px"><button>Volver</button></a>
    <a href="usuarioCrear.php" style="margin: 5px"><button>Nuevo usuario</button></a>
    <a href="usuarioBuscar.php" style="margin: 5px"><button>Buscar usuario</button></a>
</body>
</html>

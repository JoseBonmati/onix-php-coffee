<?php

    require_once "../utilidades/conectar_db.php";
    $con = conectar();
    session_start();

    // Restrict access: only logged users
    if (!isset($_SESSION["id"])) {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Only administrators can activate/deactivate others.
    // Users can only deactivate themselves.
    $admin = ($_SESSION["rol"] === "administrador");

    if ($admin) {
        $id = $_GET["id"] ?? null;
    } else {
        $id = $_SESSION["id"];
    }

    $action = $_GET["action"] ?? null;

    if (!$id || !$action) {
        header("Location: ../index.php");
        exit;
    }

    // Access restrictions:
    // 1. Non-admin users cannot deactivate others.
    // 2. Admins cannot deactivate themselves.
    if ((!$admin && $_SESSION["id"] != $id) || ($admin && $_SESSION["id"] == $id && $action === "desactivar")) {
        header("Location: login.php?acceso=denegado");
        exit;
    }

    // Determine new status
    if ($action === "desactivar") {
        $newStatus = "inactivo";
    } elseif ($action === "activar" && $admin) {
        $newStatus = "activo";
    } else {
        header("Location: ../index.php");
        exit;
    }
    
    // Update status
    $query = $con->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
    $query->execute([
        ":estado" => $newStatus,
        ":id" => $id
    ]);

    // If a normal user is deactivated log out
    if (!$admin && $action === "desactivar") {
        session_unset();
        session_destroy();
        header("Location: login.php?cuentaDesactivada=1");
        exit;
    }

    // Return to user profile
    if (isset($_GET["onlyMine"]) && $_GET["onlyMine"] == 1) { 
        header("Location: perfil.php?estadoCambiado=1"); 
        exit; 
    }

    // Return to user editing
    header("Location: usuarioEditar.php?id=" . urlencode($id) . "&estadoCambiado=1");
    exit;

?>



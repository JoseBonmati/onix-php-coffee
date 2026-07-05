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

    // Only administrators can activate/deactivate others.
    // Users can only deactivate themselves.
    $isAdmin = ($_SESSION["role"] === "admin");

    if ($isAdmin) {
        $id = $_GET["id"] ?? null;
    } else {
        $id = $_SESSION["id"];
    }

    $action = $_GET["action"] ?? null;

    if (!$id || !$action) {
        header("Location: /index.php");
        exit;
    }

    // Access restrictions:
    // 1. Non-admin users cannot deactivate others.
    // 2. Admins cannot deactivate themselves.
    if ((!$isAdmin && $_SESSION["id"] != $id) || ($isAdmin && $_SESSION["id"] == $id && $action === "deactivate")) {
        header("Location: /users/login.php?error=access_denied");
        exit;
    }

    // Determine new status (DB values remain in Spanish)
    if ($action === "deactivate") {
        $newStatus = "inactivo";
    } elseif ($action === "activate" && $isAdmin) {
        $newStatus = "activo";
    } else {
        header("Location: /index.php");
        exit;
    }
    
    // Update status
    $query = $db->prepare("UPDATE usuarios SET estado = :status WHERE id = :id");
    $query->execute([
        ":status" => $newStatus,
        ":id" => $id
    ]);

    // If a normal user is deactivated, log them out completely
    if (!$isAdmin && $action === "deactivate") {
        session_unset();
        session_destroy();
        header("Location: /users/login.php?account_deactivated=1");
        exit;
    }

    // Return to user profile if action was done from there
    if (isset($_GET["only_mine"]) && $_GET["only_mine"] == 1) { 
        header("Location: /users/profile.php?status_changed=1"); 
        exit; 
    }

    // Otherwise, return to user editing interface
    header("Location: /users/user_edit.php?id=" . urlencode((string)$id) . "&status_changed=1");
    exit;

?>
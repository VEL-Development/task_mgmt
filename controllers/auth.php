<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    
    if ($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['full_name'] = $user->full_name;
        $_SESSION['role'] = $user->role;
        header("Location: index.php?action=dashboard");
    } else {
        header("Location: index.php?action=login&error=1");
    }
} elseif (isset($_POST['action']) && $_POST['action'] == 'register') {
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    $user->full_name = $_POST['full_name'];
    
    if ($user->register()) {
        header("Location: index.php?action=login&success=1");
    } else {
        header("Location: index.php?action=register&error=1");
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: ../index.php?action=login");
    exit;
}
?>
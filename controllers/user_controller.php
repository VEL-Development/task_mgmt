<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($_POST['action'] === 'create') {
    $user->full_name = $_POST['full_name'];
    $user->username = $_POST['username'];
    $user->email = $_POST['email'];
    $user->password = $_POST['password'];
    $user->role = $_POST['role'];
    
    if ($user->register()) {
        echo 'success';
    } else {
        echo 'error';
    }
} elseif ($_POST['action'] === 'delete') {
    if ($user->deleteUser($_POST['id'])) {
        echo 'success';
    } else {
        echo 'error';
    }
}
?>
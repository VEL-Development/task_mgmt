<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /task_mgmt/index.php?action=login');
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Update basic info
    $query = "UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$full_name, $email, $username, $user_id]);
    
    // Update password if provided
    if (!empty($new_password) && !empty($current_password)) {
        $userData = $user->getById($user_id);
        if (password_verify($current_password, $userData['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$hashed_password, $user_id]);
        } else {
            header('Location: /task_mgmt/index.php?action=profile&error=Invalid current password');
            exit();
        }
    }
    
    if ($result) {
        $_SESSION['full_name'] = $full_name;
        header('Location: /task_mgmt/index.php?action=profile&success=Profile updated successfully');
    } else {
        header('Location: /task_mgmt/index.php?action=profile&error=Failed to update profile');
    }
    exit();
}

header('Location: /task_mgmt/index.php?action=profile');
exit();
?>
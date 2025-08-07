<?php
session_start();
include_once 'config/database.php';
include_once 'models/User.php';
include_once 'models/Task.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

if (!isset($_SESSION['user_id']) && $action != 'login' && $action != 'auth') {
    header("Location: index.php?action=login");
    exit();
}

switch($action) {
    case 'login':
        include 'views/login.php';
        break;

    case 'auth':
        include 'controllers/auth.php';
        break;
    case 'logout':
        session_destroy();
        header("Location: index.php?action=login");
        break;
    case 'dashboard':
        include 'views/dashboard_enhanced.php';
        break;
    case 'tasks':
        include 'controllers/task_controller.php';
        break;
    case 'create_task':
        include 'views/create_task_enhanced.php';
        break;
    case 'edit_task':
        include 'views/edit_task_enhanced.php';
        break;
    case 'task_details':
        include 'views/task_details_enhanced.php';
        break;
    case 'reports':
        include 'views/reports.php';
        break;
    case 'tasks_list':
        include 'views/tasks_list.php';
        break;
    case 'user_dashboard':
        include 'views/user_dashboard.php';
        break;
    case 'user_management':
        include 'views/user_management.php';
        break;
    case 'export_csv':
        include 'controllers/export_controller.php';
        break;
    default:
        include 'views/dashboard_enhanced.php';
}
?>
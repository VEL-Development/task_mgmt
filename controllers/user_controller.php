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
require_once __DIR__ . '/../models/TaskEnhanced.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$task = new TaskEnhanced($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    } elseif ($_POST['action'] === 'update') {
        if ($user->updateUser($_POST['user_id'], $_POST['full_name'], $_POST['username'], $_POST['email'], $_POST['role'])) {
            echo 'success';
        } else {
            echo 'error';
        }
    } elseif ($_POST['action'] === 'toggle_status') {
        if ($user->toggleUserStatus($_POST['id'], $_POST['status'])) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_GET['action'] === 'get') {
        $userData = $user->getUserById($_GET['id']);
        header('Content-Type: application/json');
        echo json_encode($userData);
    } elseif ($_GET['action'] === 'dashboard') {
        $userId = $_GET['id'];
        $userData = $user->getUserById($userId);
        $userTasks = $task->getUserTaskStats($userId);
        $recentTasks = $task->getUserRecentTasks($userId, 5);
        
        echo '<div class="user-dashboard-content">';
        echo '<div class="dashboard-header">';
        echo '<div class="user-avatar-dashboard">' . strtoupper(substr($userData['full_name'], 0, 2)) . '</div>';
        echo '<div class="user-details">';
        echo '<h3>' . htmlspecialchars($userData['full_name']) . '</h3>';
        echo '<p>@' . htmlspecialchars($userData['username']) . ' • ' . ucfirst(str_replace('_', ' ', $userData['role'])) . '</p>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="dashboard-stats">';
        echo '<div class="stat-card"><div class="stat-number">' . ($userTasks['total'] ?? 0) . '</div><div class="stat-label">Total Tasks</div></div>';
        echo '<div class="stat-card"><div class="stat-number">' . ($userTasks['completed'] ?? 0) . '</div><div class="stat-label">Completed</div></div>';
        echo '<div class="stat-card"><div class="stat-number">' . ($userTasks['pending'] ?? 0) . '</div><div class="stat-label">Pending</div></div>';
        echo '<div class="stat-card"><div class="stat-number">' . ($userTasks['in_progress'] ?? 0) . '</div><div class="stat-label">In Progress</div></div>';
        echo '</div>';
        
        if (!empty($recentTasks)) {
            echo '<div class="recent-tasks">';
            echo '<h4><i class="fas fa-clock"></i> Recent Tasks</h4>';
            echo '<div class="task-list">';
            foreach ($recentTasks as $t) {
                echo '<div class="task-item-mini">';
                echo '<div class="task-info">';
                echo '<div class="task-title">' . htmlspecialchars($t['title']) . '</div>';
                echo '<div class="task-meta">';
                echo '<span class="status-badge status-' . $t['status'] . '">' . ucfirst(str_replace('_', ' ', $t['status'])) . '</span>';
                echo '<span class="priority-badge priority-' . $t['priority'] . '">' . ucfirst($t['priority']) . '</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}
?>
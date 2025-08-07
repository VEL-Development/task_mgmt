<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /task_mgmt/index.php?action=login');
    exit();
}

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_SESSION['user_id'];
    $settings = [
        'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
        'due_date_reminders' => isset($_POST['due_date_reminders']) ? 1 : 0,
        'status_updates' => isset($_POST['status_updates']) ? 1 : 0,
        'theme' => $_POST['theme'],
        'language' => $_POST['language'],
        'tasks_per_page' => $_POST['tasks_per_page'],
        'default_view' => $_POST['default_view'],
        'show_profile' => isset($_POST['show_profile']) ? 1 : 0,
        'activity_tracking' => isset($_POST['activity_tracking']) ? 1 : 0,
        'session_timeout' => $_POST['session_timeout']
    ];
    
    // Check if user settings exist
    $query = "SELECT id FROM user_settings WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    
    if ($stmt->fetch()) {
        // Update existing settings
        $query = "UPDATE user_settings SET 
                  email_notifications = ?, due_date_reminders = ?, status_updates = ?,
                  theme = ?, language = ?, tasks_per_page = ?, default_view = ?,
                  show_profile = ?, activity_tracking = ?, session_timeout = ?
                  WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            $settings['email_notifications'], $settings['due_date_reminders'], $settings['status_updates'],
            $settings['theme'], $settings['language'], $settings['tasks_per_page'], $settings['default_view'],
            $settings['show_profile'], $settings['activity_tracking'], $settings['session_timeout'],
            $user_id
        ]);
    } else {
        // Insert new settings
        $query = "INSERT INTO user_settings 
                  (user_id, email_notifications, due_date_reminders, status_updates, theme, language, 
                   tasks_per_page, default_view, show_profile, activity_tracking, session_timeout) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            $user_id, $settings['email_notifications'], $settings['due_date_reminders'], $settings['status_updates'],
            $settings['theme'], $settings['language'], $settings['tasks_per_page'], $settings['default_view'],
            $settings['show_profile'], $settings['activity_tracking'], $settings['session_timeout']
        ]);
    }
    
    if ($result) {
        header('Location: /task_mgmt/index.php?action=settings&success=Settings saved successfully');
    } else {
        header('Location: /task_mgmt/index.php?action=settings&error=Failed to save settings');
    }
    exit();
}

header('Location: /task_mgmt/index.php?action=settings');
exit();
?>
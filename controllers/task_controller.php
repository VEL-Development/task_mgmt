<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'models/TaskEnhanced.php';

$database = new Database();
$db = $database->getConnection();
$task = new TaskEnhanced($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['task_action'];
    
    if ($action == 'create') {
        $task->title = $_POST['title'];
        $task->description = $_POST['description'] ?? '';
        $task->status = $_POST['status'] ?? 'pending';
        $task->priority = $_POST['priority'] ?? 'medium';
        $task->assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
        $task->created_by = $_SESSION['user_id'];
        $task->due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $task->start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        
        if ($task_id = $task->create()) {
            // Handle file uploads
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                        $original_name = $_FILES['attachments']['name'][$key];
                        $file_size = $_FILES['attachments']['size'][$key];
                        $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $file_ext;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($tmp_name, $filepath)) {
                            $task->addAttachment($task_id, $filename, $original_name, $file_size, $_SESSION['user_id']);
                        }
                    }
                }
            }
            
            header("Location: index.php?success=created");
            exit;
        } else {
            error_log("Task creation failed for user: " . $_SESSION['user_id']);
            header("Location: index.php?error=Failed to create task");
            exit;
        }
    } elseif ($action == 'update') {
        $task->id = $_POST['task_id'];
        $task->title = $_POST['title'];
        $task->description = $_POST['description'];
        $task->status = $_POST['status'];
        $task->priority = $_POST['priority'];
        $task->assigned_to = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
        $task->due_date = $_POST['due_date'] ?: null;
        $task->start_date = $_POST['start_date'] ?: null;
        
        if ($task->update()) {
            // Handle file uploads for update
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                        $original_name = $_FILES['attachments']['name'][$key];
                        $file_size = $_FILES['attachments']['size'][$key];
                        $file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $file_ext;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($tmp_name, $filepath)) {
                            $task->addAttachment($_POST['task_id'], $filename, $original_name, $file_size, $_SESSION['user_id']);
                        }
                    }
                }
            }
            
            header("Location: index.php?success=updated");
            exit;
        } else {
            header("Location: index.php?error=Failed to update task");
            exit;
        }
    } elseif ($action == 'delete') {
        $task->id = $_POST['task_id'];
        if ($task->delete()) {
            header("Location: index.php?action=tasks_list&success=deleted");
            exit;
        } else {
            header("Location: index.php?action=tasks_list&error=Failed to delete task");
            exit;
        }
    }
}

header("Location: index.php");
exit;
?>
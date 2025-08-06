<?php
session_start();
require_once '../config/database.php';
require_once '../models/TaskEnhanced.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$task = new TaskEnhanced($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['task_id']) || !isset($_FILES['attachments'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    $task_id = $_POST['task_id'];
    $uploaded_files = [];
    $errors = [];
    
    $upload_dir = '../uploads/';
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
            
            // Validate file size (max 10MB)
            if ($file_size > 10 * 1024 * 1024) {
                $errors[] = "File {$original_name} is too large (max 10MB)";
                continue;
            }
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'];
            if (!in_array(strtolower($file_ext), $allowed_types)) {
                $errors[] = "File type not allowed for {$original_name}";
                continue;
            }
            
            if (move_uploaded_file($tmp_name, $filepath)) {
                if ($task->addAttachment($task_id, $filename, $original_name, $file_size, $_SESSION['user_id'])) {
                    $uploaded_files[] = $original_name;
                } else {
                    $errors[] = "Failed to save {$original_name} to database";
                    unlink($filepath);
                }
            } else {
                $errors[] = "Failed to upload {$original_name}";
            }
        }
    }
    
    if (count($uploaded_files) > 0) {
        echo json_encode([
            'success' => true, 
            'message' => count($uploaded_files) . ' file(s) uploaded successfully',
            'uploaded' => $uploaded_files,
            'errors' => $errors
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No files uploaded', 'errors' => $errors]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing attachment ID']);
        exit;
    }
    
    if ($task->deleteAttachment($input['id'])) {
        echo json_encode(['success' => true, 'message' => 'Attachment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete attachment']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
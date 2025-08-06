<?php
session_start();
require_once '../config/database.php';
require_once '../models/Task.php';
require_once '../models/TaskEnhanced.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['task_id']) || !isset($_FILES['files'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$task = new TaskEnhanced($db);

$task_id = $_POST['task_id'];
$upload_dir = '../uploads/';
$allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
$max_size = 5 * 1024 * 1024; // 5MB

$uploaded_files = [];
$errors = [];

foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
    if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
        $original_name = $_FILES['files']['name'][$key];
        $file_size = $_FILES['files']['size'][$key];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = "File type not allowed: $original_name";
            continue;
        }
        
        if ($file_size > $max_size) {
            $errors[] = "File too large: $original_name";
            continue;
        }
        
        $filename = uniqid() . '_' . time() . '.' . $file_ext;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($tmp_name, $filepath)) {
            $mime_type = mime_content_type($filepath);
            if ($task->addAttachment($task_id, $filename, $original_name, $file_size, $mime_type, $_SESSION['user_id'])) {
                $uploaded_files[] = $original_name;
            } else {
                $errors[] = "Database error for: $original_name";
                unlink($filepath);
            }
        } else {
            $errors[] = "Upload failed for: $original_name";
        }
    }
}

if (count($uploaded_files) > 0) {
    echo json_encode([
        'success' => true, 
        'message' => count($uploaded_files) . ' file(s) uploaded successfully',
        'files' => $uploaded_files,
        'errors' => $errors
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'No files uploaded',
        'errors' => $errors
    ]);
}
?>
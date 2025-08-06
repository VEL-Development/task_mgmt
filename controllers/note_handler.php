<?php
session_start();
require_once '../config/database.php';
require_once '../models/TaskEnhanced.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['task_id']) || !isset($_POST['note'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$task = new TaskEnhanced($db);

$task_id = $_POST['task_id'];
$note = trim($_POST['note']);
$is_private = isset($_POST['is_private']) ? 1 : 0;

if (empty($note)) {
    echo json_encode(['success' => false, 'message' => 'Note cannot be empty']);
    exit;
}

if ($task->addNote($task_id, $_SESSION['user_id'], $note, $is_private)) {
    echo json_encode(['success' => true, 'message' => 'Note added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add note']);
}
?>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's tasks
$query = "SELECT t.*, u.username as assigned_to_name FROM tasks t 
          LEFT JOIN users u ON t.assigned_to = u.id 
          WHERE t.assigned_to = ? 
          ORDER BY t.due_date ASC, t.priority DESC";
$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="my_tasks_' . date('Y-m-d') . '.csv"');

// Create file pointer
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Task ID',
    'Title',
    'Description',
    'Status',
    'Priority',
    'Start Date',
    'Due Date',
    'Created Date',
    'Assigned To'
]);

// Add task data
foreach ($tasks as $task) {
    fputcsv($output, [
        $task['id'],
        $task['title'],
        $task['description'],
        ucfirst(str_replace('_', ' ', $task['status'])),
        ucfirst($task['priority']),
        date('Y-m-d', strtotime($task['start_date'])),
        date('Y-m-d', strtotime($task['due_date'])),
        date('Y-m-d H:i:s', strtotime($task['created_at'])),
        $task['assigned_to_name']
    ]);
}

fclose($output);
exit();
?>
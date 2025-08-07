<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit();
}

require_once 'config/database.php';
require_once 'models/TaskEnhanced.php';

$task = new TaskEnhanced($db);

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$assigned_filter = $_GET['assigned'] ?? '';
$search = $_GET['search'] ?? '';
$start_date_filter = $_GET['start_date'] ?? '';
$due_date_filter = $_GET['due_date'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "t.status = ?";
    $params[] = $status_filter;
}
if ($priority_filter) {
    $where_conditions[] = "t.priority = ?";
    $params[] = $priority_filter;
}
if ($assigned_filter) {
    $where_conditions[] = "t.assigned_to = ?";
    $params[] = $assigned_filter;
}
if ($search) {
    $where_conditions[] = "(t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($start_date_filter) {
    $where_conditions[] = "DATE(t.start_date) >= ?";
    $params[] = $start_date_filter;
}
if ($due_date_filter) {
    $where_conditions[] = "DATE(t.due_date) <= ?";
    $params[] = $due_date_filter;
}

// Get all tasks (no pagination for export)
$tasks = $task->getTasksWithPagination($where_conditions, $params, 1, 10000);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="tasks_export_' . date('Y-m-d_H-i-s') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV header
fputcsv($output, [
    'ID',
    'Title',
    'Description',
    'Status',
    'Priority',
    'Assigned To',
    'Start Date',
    'Due Date',
    'Created At',
    'Updated At'
]);

// Write task data
foreach ($tasks as $taskItem) {
    fputcsv($output, [
        $taskItem['id'],
        $taskItem['title'],
        $taskItem['description'],
        ucfirst(str_replace('_', ' ', $taskItem['status'])),
        ucfirst($taskItem['priority']),
        $taskItem['assigned_name'] ?: 'Unassigned',
        $taskItem['start_date'] ? date('Y-m-d', strtotime($taskItem['start_date'])) : '',
        $taskItem['due_date'] ? date('Y-m-d', strtotime($taskItem['due_date'])) : '',
        date('Y-m-d H:i:s', strtotime($taskItem['created_at'])),
        $taskItem['updated_at'] ? date('Y-m-d H:i:s', strtotime($taskItem['updated_at'])) : ''
    ]);
}

fclose($output);
exit();
?>
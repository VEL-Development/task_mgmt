<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?action=login');
    exit();
}

require_once 'config/database.php';
require_once 'models/TaskEnhanced.php';

$task = new TaskEnhanced($db);

// Get filter parameters
$status_filter = isset($_GET['status']) ? (is_array($_GET['status']) ? implode(',', $_GET['status']) : $_GET['status']) : '';
$priority_filter = isset($_GET['priority']) ? (is_array($_GET['priority']) ? implode(',', $_GET['priority']) : $_GET['priority']) : '';
$assigned_filter = isset($_GET['assigned']) ? (is_array($_GET['assigned']) ? implode(',', $_GET['assigned']) : $_GET['assigned']) : '';
$search = $_GET['search'] ?? '';
$start_date_filter = $_GET['start_date'] ?? '';
$due_date_filter = $_GET['due_date'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $status_ids = explode(',', $status_filter);
    $placeholders = str_repeat('?,', count($status_ids) - 1) . '?';
    $where_conditions[] = "t.status_id IN ($placeholders)";
    $params = array_merge($params, $status_ids);
}
if ($priority_filter) {
    $priorities = explode(',', $priority_filter);
    $placeholders = str_repeat('?,', count($priorities) - 1) . '?';
    $where_conditions[] = "t.priority IN ($placeholders)";
    $params = array_merge($params, $priorities);
}
if ($assigned_filter !== '') {
    $assigned_ids = explode(',', $assigned_filter);
    $assigned_conditions = [];
    foreach ($assigned_ids as $assigned_id) {
        if ($assigned_id === '0') {
            $assigned_conditions[] = "t.assigned_to IS NULL";
        } else {
            $assigned_conditions[] = "t.assigned_to = ?";
            $params[] = $assigned_id;
        }
    }
    if (!empty($assigned_conditions)) {
        $where_conditions[] = "(" . implode(" OR ", $assigned_conditions) . ")";
    }
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
header('Content-Type: application/csv');
$filename = "tasks_export_" . date('Y-m-d_H-i-s') . ".csv";
header('Content-Disposition: attachment; filename="' . $filename . '";');

// Open output stream
$f = fopen('php://output', 'w');

// Write CSV header
fputcsv($f, [
    'ID',
    'Title & Description',
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
    $title = str_replace(':', '-', $taskItem['title']);
    $merged_title = $title . ':' . $taskItem['description'];
    
    fputcsv($f, [
        $taskItem['id'],
        $merged_title,
        ucfirst(str_replace('_', ' ', $taskItem['status'])),
        ucfirst($taskItem['priority']),
        $taskItem['assigned_name'] ?: 'Unassigned',
        $taskItem['start_date'] ? date('Y-m-d', strtotime($taskItem['start_date'])) : '',
        $taskItem['due_date'] ? date('Y-m-d', strtotime($taskItem['due_date'])) : '',
        date('Y-m-d H:i:s', strtotime($taskItem['created_at'])),
        $taskItem['updated_at'] ? date('Y-m-d H:i:s', strtotime($taskItem['updated_at'])) : ''
    ]);
}

fclose($f);
exit();
?>
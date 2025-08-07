<?php
require_once 'models/TaskStatus.php';

$statusModel = new TaskStatus($db);

if ($_GET['action'] == 'get_status') {
    $status = $statusModel->getStatusById($_GET['id']);
    header('Content-Type: application/json');
    echo json_encode($status);
    exit();
}

if ($_GET['action'] == 'create_status') {
    $name = $_POST['name'];
    $group_status = $_POST['group_status'];
    $color = $_POST['color'];
    $sort_order = $_POST['sort_order'];
    
    if ($statusModel->createStatus($name, $group_status, $color, $sort_order)) {
        $response = ['success' => true, 'message' => 'Status created successfully'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to create status'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_GET['action'] == 'update_status') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $group_status = $_POST['group_status'];
    $color = $_POST['color'];
    $sort_order = $_POST['sort_order'];
    
    if ($statusModel->updateStatus($id, $name, $group_status, $color, $sort_order)) {
        $response = ['success' => true, 'message' => 'Status updated successfully'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to update status'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_GET['action'] == 'delete_status') {
    $id = $_GET['id'];
    
    if ($statusModel->deleteStatus($id)) {
        header('Location: index.php?action=task_status_management&success=deleted');
    } else {
        header('Location: index.php?action=task_status_management&error=Failed to delete status');
    }
    exit();
}
?>
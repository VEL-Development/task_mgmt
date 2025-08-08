<?php
require_once 'config/database.php';

try {
    // Update tasks with null status_id to use default status (id=1)
    $query = "UPDATE tasks SET status_id = 1 WHERE status_id IS NULL";
    $stmt = $db->prepare($query);
    $result = $stmt->execute();
    
    if ($result) {
        echo "Successfully updated tasks with null status_id\n";
        
        // Show count of updated records
        $query = "SELECT COUNT(*) as count FROM tasks WHERE status_id = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Total tasks with default status: " . $count . "\n";
    } else {
        echo "Failed to update tasks\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
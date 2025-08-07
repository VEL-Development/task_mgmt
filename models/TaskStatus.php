<?php
class TaskStatus {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function getAllStatuses() {
        $query = "SELECT * FROM task_statuses ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStatusById($id) {
        $query = "SELECT * FROM task_statuses WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createStatus($name, $group_status, $color, $sort_order) {
        $query = "INSERT INTO task_statuses (name, group_status, color, sort_order) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$name, $group_status, $color, $sort_order]);
    }
    
    public function updateStatus($id, $name, $group_status, $color, $sort_order) {
        $query = "UPDATE task_statuses SET name = ?, group_status = ?, color = ?, sort_order = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$name, $group_status, $color, $sort_order, $id]);
    }
    
    public function deleteStatus($id) {
        $query = "DELETE FROM task_statuses WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    
    public function getGroupStatuses() {
        return ['pending', 'in_progress', 'completed', 'cancelled'];
    }
}
?>
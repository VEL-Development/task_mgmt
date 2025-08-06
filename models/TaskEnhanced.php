<?php
require_once 'Task.php';

class TaskEnhanced extends Task {
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    public function read() {
        $query = "SELECT t.*, 
                         u1.full_name as assigned_name,
                         u2.full_name as creator_name
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getById($id) {
        $query = "SELECT t.*, 
                         u1.full_name as assigned_name,
                         u2.full_name as creator_name
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  WHERE t.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getStatistics() {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'priority_low' => 0,
            'priority_medium' => 0,
            'priority_high' => 0,
            'priority_urgent' => 0
        ];
        
        // Status statistics
        $query = "SELECT status, COUNT(*) as count FROM tasks GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
            $stats['total'] += $row['count'];
        }
        
        // Priority statistics
        $query = "SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['priority_' . $row['priority']] = $row['count'];
        }
        
        return $stats;
    }
    
    public function getChartData() {
        return $this->getStatistics();
    }
    
    public function getNotes($task_id, $user_id = null) {
        if ($user_id) {
            $query = "SELECT n.*, u.full_name as author_name FROM task_notes n 
                      JOIN users u ON n.user_id = u.id 
                      WHERE n.task_id = ? AND (n.is_private = 0 OR n.user_id = ?) 
                      ORDER BY n.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$task_id, $user_id]);
        } else {
            $query = "SELECT n.*, u.full_name as author_name FROM task_notes n 
                      JOIN users u ON n.user_id = u.id 
                      WHERE n.task_id = ? AND n.is_private = 0 
                      ORDER BY n.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$task_id]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addNote($task_id, $user_id, $note, $is_private = 0) {
        $query = "INSERT INTO task_notes (task_id, user_id, note, is_private) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$task_id, $user_id, $note, $is_private]);
        
        if ($result) {
            $this->logAudit($task_id, $user_id, 'added note', 'notes', '', $note);
        }
        
        return $result;
    }
    
    public function getAttachments($task_id) {
        $query = "SELECT a.*, u.full_name as uploader_name FROM task_attachments a 
                  JOIN users u ON a.uploaded_by = u.id 
                  WHERE a.task_id = ? ORDER BY a.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$task_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addAttachment($task_id, $filename, $original_name, $file_size, $uploaded_by) {
        $query = "INSERT INTO task_attachments (task_id, filename, original_name, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$task_id, $filename, $original_name, $file_size, $uploaded_by]);
        
        if ($result) {
            $this->logAudit($task_id, $uploaded_by, 'uploaded attachment', 'attachments', '', $original_name);
        }
        
        return $result;
    }
    
    public function deleteAttachment($id) {
        $query = "SELECT * FROM task_attachments WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attachment) {
            $query = "DELETE FROM task_attachments WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                $filepath = 'uploads/' . $attachment['filename'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                
                $this->logAudit($attachment['task_id'], $_SESSION['user_id'], 'deleted attachment', 'attachments', $attachment['original_name'], '');
            }
            
            return $result;
        }
        
        return false;
    }
    
    public function getAuditLog($task_id) {
        $query = "SELECT a.*, u.full_name as user_name FROM task_audit a 
                  JOIN users u ON a.user_id = u.id 
                  WHERE a.task_id = ? ORDER BY a.created_at DESC LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$task_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function logAudit($task_id, $user_id, $action, $field_name = null, $old_value = null, $new_value = null) {
        $query = "INSERT INTO task_audit (task_id, user_id, action, field_name, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$task_id, $user_id, $action, $field_name, $old_value, $new_value]);
    }
    
    public function create() {
        try {
            $query = "INSERT INTO tasks (title, description, status, priority, assigned_to, created_by, due_date, start_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $this->title,
                $this->description,
                $this->status,
                $this->priority,
                $this->assigned_to,
                $this->created_by,
                $this->due_date,
                $this->start_date
            ]);
            
            if ($result) {
                $task_id = $this->conn->lastInsertId();
                if ($task_id) {
                    $this->logAudit($task_id, $this->created_by, 'created');
                }
                return $task_id;
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Task creation error: " . $e->getMessage());
            return false;
        }
    }
    
    public function update() {
        try {
            $current = $this->getById($this->id);
            
            $query = "UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, assigned_to = ?, due_date = ?, start_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $this->title,
                $this->description,
                $this->status,
                $this->priority,
                $this->assigned_to,
                $this->due_date,
                $this->start_date,
                $this->id
            ]);
            
            if ($result && $current) {
                $changes = [
                    'title' => [$current['title'], $this->title],
                    'status' => [$current['status'], $this->status],
                    'priority' => [$current['priority'], $this->priority]
                ];
                
                foreach ($changes as $field => $values) {
                    if ($values[0] != $values[1]) {
                        $this->logAudit($this->id, $_SESSION['user_id'], 'updated', $field, $values[0], $values[1]);
                    }
                }
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Task update error: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete() {
        $query = "DELETE FROM tasks WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }
    
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    public function getTasksWithPagination($where_conditions = [], $params = [], $page = 1, $per_page = 10) {
        $offset = ($page - 1) * $per_page;
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $query = "SELECT t.*, u1.full_name as assigned_name, u2.full_name as creator_name
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  $where_clause
                  ORDER BY t.created_at DESC
                  LIMIT $per_page OFFSET $offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTasksCount($where_conditions = [], $params = []) {
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $query = "SELECT COUNT(*) as total FROM tasks t $where_clause";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
?>
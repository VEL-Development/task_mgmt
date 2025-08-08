<?php
require_once 'Task.php';

class TaskEnhanced extends Task {
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    public function read() {
        $query = "SELECT t.*, 
                         u1.full_name as assigned_name,
                         u2.full_name as creator_name,
                         ts.name as status_name,
                         ts.color as status_color,
                         ts.group_status
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getById($id) {
        $query = "SELECT t.*, 
                         u1.full_name as assigned_name,
                         u2.full_name as creator_name,
                         ts.name as status_name,
                         ts.color as status_color,
                         ts.group_status
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
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
        
        // Status statistics using group_status
        $query = "SELECT ts.group_status, COUNT(*) as count 
                  FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id 
                  GROUP BY ts.group_status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['group_status']) {
                $stats[$row['group_status']] = $row['count'];
                $stats['total'] += $row['count'];
            }
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
        $query = "SELECT a.*, u.full_name as user_name,
                         ts1.name as old_status_name, ts1.color as old_status_color,
                         ts2.name as new_status_name, ts2.color as new_status_color
                  FROM task_audit a 
                  JOIN users u ON a.user_id = u.id 
                  LEFT JOIN task_statuses ts1 ON a.field_name = 'status_id' AND a.old_value = ts1.id
                  LEFT JOIN task_statuses ts2 ON a.field_name = 'status_id' AND a.new_value = ts2.id
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
            $query = "INSERT INTO tasks (title, description, status_id, priority, assigned_to, created_by, due_date, start_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $this->title,
                $this->description,
                $this->status_id,
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
            
            $query = "UPDATE tasks SET title = ?, description = ?, status_id = ?, priority = ?, assigned_to = ?, due_date = ?, start_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                $this->title,
                $this->description,
                $this->status_id,
                $this->priority,
                $this->assigned_to,
                $this->due_date,
                $this->start_date,
                $this->id
            ]);
            
            if ($result && $current) {
                $changes = [
                    'title' => [$current['title'], $this->title],
                    'status_id' => [$current['status_id'], $this->status_id],
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
        
        $query = "SELECT t.*, 
                         u1.full_name as assigned_name, 
                         u2.full_name as creator_name,
                         ts.name as status_name,
                         ts.color as status_color,
                         ts.group_status
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  $where_clause
                  ORDER BY t.created_at DESC
                  LIMIT " . (int)$per_page . " OFFSET " . (int)$offset . "";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTasksCount($where_conditions = [], $params = []) {
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $query = "SELECT COUNT(*) as total FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id 
                  $where_clause";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getUserTaskStats($user_id) {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'overdue' => 0
        ];
        
        $query = "SELECT ts.group_status, COUNT(*) as count 
                  FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id 
                  WHERE t.assigned_to = ? 
                  GROUP BY ts.group_status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['group_status']) {
                $stats[$row['group_status']] = $row['count'];
                $stats['total'] += $row['count'];
            }
        }
        
        // Get overdue count
        $overdueQuery = "SELECT COUNT(*) as overdue_count 
                        FROM tasks t 
                        LEFT JOIN task_statuses ts ON t.status_id = ts.id 
                        WHERE t.assigned_to = ? 
                        AND DATE(t.due_date) < CURDATE() 
                        AND ts.group_status NOT IN ('completed', 'cancelled')";
        $overdueStmt = $this->conn->prepare($overdueQuery);
        $overdueStmt->execute([$user_id]);
        $overdueResult = $overdueStmt->fetch(PDO::FETCH_ASSOC);
        $stats['overdue'] = $overdueResult['overdue_count'] ?? 0;
        
        return $stats;
    }
    
    public function getUserRecentTasks($user_id, $limit = 5) {
        $query = "SELECT t.id, t.title, t.priority, t.created_at, t.start_date,
                         ts.name as status_name,
                         ts.color as status_color,
                         ts.group_status
                  FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  WHERE t.assigned_to = ? 
                  ORDER BY t.created_at DESC 
                  LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserTasks($user_id, $limit = 5) {
        $query = "SELECT t.*, 
                         ts.name as status_name,
                         ts.color as status_color,
                         ts.group_status
                  FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  WHERE t.assigned_to = ? 
                  ORDER BY t.created_at DESC 
                  LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTodayTasks($user_id) {
        $query = "SELECT t.*, 
                         ts.name as status_name,
                         ts.color as status_color,
                         ts.group_status
                  FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  WHERE t.assigned_to = ? 
                  AND (DATE(t.start_date) = CURDATE() OR DATE(t.due_date) = CURDATE())
                  ORDER BY t.priority DESC, t.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllStatuses() {
        $query = "SELECT * FROM task_statuses ORDER BY id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStatusesForCreation() {
        $query = "SELECT * FROM task_statuses WHERE group_status IN ('pending', 'in_progress', 'completed') ORDER BY id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllUserTasks($user_id, $limit = 50) {
        $query = "SELECT t.*, 
                         ts.name as status_name, 
                         ts.color as status_color, 
                         ts.group_status, 
                         u.full_name as creator_name
                  FROM tasks t 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  LEFT JOIN users u ON t.created_by = u.id
                  WHERE t.assigned_to = ?
                  ORDER BY t.created_at DESC 
                  LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function readRecentFiltered($status_filter = '', $priority_filter = '', $limit = 6) {
        $where_conditions = [];
        $params = [];
        
        if ($status_filter) {
            $where_conditions[] = "t.status_id = ?";
            $params[] = $status_filter;
        }
        if ($priority_filter) {
            $where_conditions[] = "t.priority = ?";
            $params[] = $priority_filter;
        }
        
        $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $query = "SELECT t.*, u1.full_name as assigned_name, u2.full_name as creator_name,
                         ts.name as status_name, ts.color as status_color, ts.group_status
                  FROM tasks t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  $where_clause
                  ORDER BY t.created_at DESC 
                  LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function getWeeklyProductivity() {
        $query = "SELECT 
                    DATE(t.created_at) as date,
                    COUNT(*) as created_count,
                    SUM(CASE WHEN ts.group_status = 'completed' THEN 1 ELSE 0 END) as completed_count
                  FROM tasks t
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY DATE(t.created_at)
                  ORDER BY DATE(t.created_at)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMonthlyWorkload() {
        $query = "SELECT 
                    WEEK(t.created_at) as week_num,
                    COUNT(*) as task_count
                  FROM tasks t
                  WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                  GROUP BY WEEK(t.created_at)
                  ORDER BY WEEK(t.created_at)
                  LIMIT 4";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPerformanceMetrics() {
        $query = "SELECT 
                    ROUND(AVG(CASE WHEN ts.group_status = 'completed' THEN 100 ELSE 70 END), 0) as quality,
                    ROUND(AVG(CASE WHEN t.due_date IS NOT NULL AND ts.group_status = 'completed' AND t.updated_at <= t.due_date THEN 95 ELSE 75 END), 0) as speed,
                    ROUND(AVG(CASE WHEN DATEDIFF(COALESCE(t.updated_at, NOW()), t.created_at) <= 7 THEN 90 ELSE 65 END), 0) as efficiency,
                    ROUND(AVG(CASE WHEN t.description IS NOT NULL AND LENGTH(t.description) > 10 THEN 95 ELSE 80 END), 0) as accuracy,
                    ROUND(AVG(CASE WHEN t.assigned_to IS NOT NULL THEN 90 ELSE 60 END), 0) as teamwork
                  FROM tasks t
                  LEFT JOIN task_statuses ts ON t.status_id = ts.id
                  WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
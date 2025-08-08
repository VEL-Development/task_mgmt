<?php
class Task {
    protected $conn;
    private $table_name = "tasks";

    public $id;
    public $title;
    public $description;
    public $status;
    public $priority;
    public $assigned_to;
    public $created_by;
    public $due_date;
    public $start_date;
    public $estimated_hours;
    public $actual_hours;
    public $assigned_name;
    public $creator_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET title=:title, description=:description, status=:status, priority=:priority, assigned_to=:assigned_to, created_by=:created_by, due_date=:due_date";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":created_by", $this->created_by);
        $stmt->bindParam(":due_date", $this->due_date);
        
        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT t.*, u1.full_name as assigned_name, u2.full_name as creator_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readRecent($limit = 6) {
        $query = "SELECT t.*, u1.full_name as assigned_name, u2.full_name as creator_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  ORDER BY t.created_at DESC LIMIT " . (int)$limit;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title=:title, description=:description, status=:status, priority=:priority, assigned_to=:assigned_to, due_date=:due_date WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":due_date", $this->due_date);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT t.*, u1.full_name as assigned_name, u2.full_name as creator_name 
                  FROM " . $this->table_name . " t 
                  LEFT JOIN users u1 ON t.assigned_to = u1.id 
                  LEFT JOIN users u2 ON t.created_by = u2.id 
                  WHERE t.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            $this->priority = $row['priority'];
            $this->assigned_to = $row['assigned_to'];
            $this->created_by = $row['created_by'];
            $this->due_date = $row['due_date'];
            $this->start_date = $row['start_date'] ?? null;
            $this->estimated_hours = $row['estimated_hours'] ?? null;
            $this->assigned_name = $row['assigned_name'];
            $this->creator_name = $row['creator_name'];
            return true;
        }
        return false;
    }
}
?>
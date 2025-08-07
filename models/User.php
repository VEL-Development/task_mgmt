<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT id, username, password, full_name, role FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->full_name = $row['full_name'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " SET username=:username, email=:email, password=:password, full_name=:full_name, role=:role";
        $stmt = $this->conn->prepare($query);
        
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":role", $this->role);
        
        return $stmt->execute();
    }

    public function getAllUsers() {
        try {
            $query = "SELECT id, username, email, full_name, role, 
                      COALESCE(status, 'active') as status, created_at 
                      FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Fallback if status column doesn't exist
            $query = "SELECT id, username, email, full_name, role, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Add default status
            foreach ($users as &$user) {
                $user['status'] = 'active';
            }
            return $users;
        }
    }
    
    public function getUserById($id) {
        try {
            $query = "SELECT id, username, email, full_name, role, 
                      COALESCE(status, 'active') as status 
                      FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Fallback if status column doesn't exist
            $query = "SELECT id, username, email, full_name, role FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $user['status'] = 'active';
            }
            return $user;
        }
    }
    
    public function updateUser($id, $full_name, $username, $email, $role) {
        $query = "UPDATE " . $this->table_name . " SET full_name = ?, username = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$full_name, $username, $email, $role, $id]);
    }
    
    public function toggleUserStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$status, $id]);
        } catch (Exception $e) {
            // If status column doesn't exist, return false
            return false;
        }
    }
    
    public function read() {
        $query = "SELECT id, username, full_name FROM " . $this->table_name . " ORDER BY full_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
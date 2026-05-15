<?php
require_once 'database.php';


class Student {
    private $conn;
    private $table = "students";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll($filters = []) {
        $query = "SELECT s.*, u.email, u.username 
                  FROM " . $this->table . " s
                  JOIN users u ON s.user_id = u.id
                  WHERE 1=1";
        
        if (!empty($filters['program'])) {
            $query .= " AND s.program = :program";
        }
        if (!empty($filters['status'])) {
            $query .= " AND s.status = :status";
        }
        if (!empty($filters['search'])) {
            $query .= " AND (s.first_name LIKE :search OR s.last_name LIKE :search OR s.phone LIKE :search)";
        }
        
        $query .= " ORDER BY s.last_name, s.first_name";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filters['program'])) {
            $stmt->bindParam(':program', $filters['program']);
        }
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $stmt->bindParam(':search', $searchTerm);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $query = "SELECT s.*, u.email, u.username 
                  FROM " . $this->table . " s
                  JOIN users u ON s.user_id = u.id
                  WHERE s.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        // Сначала создаем пользователя
        require_once 'models/User.php';
        $user = new User();
        
        $userData = [
            'username' => $data['username'],
            'password' => $data['password'],
            'email' => $data['email'],
            'role' => 'student'
        ];
        
        $userId = $user->create($userData);
        if (!$userId) {
            return false;
        }
        
        // Затем создаем студента
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, first_name, last_name, birth_date, phone, address, program, status) 
                  VALUES (:user_id, :first_name, :last_name, :birth_date, :phone, :address, :program, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':birth_date', $data['birth_date']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':program', $data['program']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET first_name = :first_name, 
                      last_name = :last_name, 
                      phone = :phone, 
                      address = :address, 
                      program = :program, 
                      status = :status,
                      birth_date = :birth_date
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':program', $data['program']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':birth_date', $data['birth_date']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function getStats() {
        $query = "SELECT COUNT(*) as total, 
                         program, 
                         status 
                  FROM " . $this->table . " 
                  GROUP BY program, status";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
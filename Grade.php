<?php
require_once 'config/database.php';

class Grade {
    private $conn;
    private $table = "grades";
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getByStudent($student_id) {
        $query = "SELECT g.*, c.name as course_name, e.academic_year 
                  FROM " . $this->table . " g
                  JOIN enrollments e ON g.enrollment_id = e.id
                  JOIN courses c ON e.course_id = c.id
                  WHERE e.student_id = :student_id
                  ORDER BY g.graded_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getByCourse($course_id) {
        $query = "SELECT g.*, s.last_name, s.first_name, e.academic_year 
                  FROM " . $this->table . " g
                  JOIN enrollments e ON g.enrollment_id = e.id
                  JOIN students s ON e.student_id = s.id
                  WHERE e.course_id = :course_id
                  ORDER BY g.grade_value DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (enrollment_id, grade_type, grade_value) 
                  VALUES (:enrollment_id, :grade_type, :grade_value)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':enrollment_id', $data['enrollment_id']);
        $stmt->bindParam(':grade_type', $data['grade_type']);
        $stmt->bindParam(':grade_value', $data['grade_value']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $grade_value) {
        $query = "UPDATE " . $this->table . " 
                  SET grade_value = :grade_value 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':grade_value', $grade_value);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    public function getAverageForStudent($student_id) {
        $query = "SELECT AVG(g.grade_value) as average, COUNT(*) as total_grades 
                  FROM " . $this->table . " g
                  JOIN enrollments e ON g.enrollment_id = e.id
                  WHERE e.student_id = :student_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
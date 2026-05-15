<?php
require_once 'models/Student.php';

class StudentController {
    private $student;
    private $user_role;
    
    public function __construct($user_role = null) {
        $this->student = new Student();
        $this->user_role = $user_role;
    }
    
    public function getAll() {
        $filters = [];
        
        if (isset($_GET['program'])) {
            $filters['program'] = $_GET['program'];
        }
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        $students = $this->student->getAll($filters);
        $this->sendResponse(200, $students);
    }
    
    public function getById($id) {
        $student = $this->student->getById($id);
        if ($student) {
            $this->sendResponse(200, $student);
        } else {
            $this->sendResponse(404, ['error' => 'Student not found']);
        }
    }
    
    public function create() {
        if ($this->user_role !== 'admin' && $this->user_role !== 'teacher') {
            $this->sendResponse(403, ['error' => 'Permission denied']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['username', 'password', 'email', 'first_name', 'last_name', 'program'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->sendResponse(400, ['error' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        $data['status'] = $data['status'] ?? 'Active';
        $data['birth_date'] = $data['birth_date'] ?? null;
        $data['phone'] = $data['phone'] ?? null;
        $data['address'] = $data['address'] ?? null;
        
        $studentId = $this->student->create($data);
        if ($studentId) {
            $this->sendResponse(201, ['message' => 'Student created successfully', 'id' => $studentId]);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to create student']);
        }
    }
    
    public function update($id) {
        if ($this->user_role !== 'admin' && $this->user_role !== 'teacher') {
            $this->sendResponse(403, ['error' => 'Permission denied']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if ($this->student->update($id, $data)) {
            $this->sendResponse(200, ['message' => 'Student updated successfully']);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to update student']);
        }
    }
    
    public function delete($id) {
        if ($this->user_role !== 'admin') {
            $this->sendResponse(403, ['error' => 'Permission denied']);
            return;
        }
        
        if ($this->student->delete($id)) {
            $this->sendResponse(200, ['message' => 'Student deleted successfully']);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to delete student']);
        }
    }
    
    public function getStats() {
        $stats = $this->student->getStats();
        $this->sendResponse(200, $stats);
    }
    
    private function sendResponse($status_code, $data) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
?>

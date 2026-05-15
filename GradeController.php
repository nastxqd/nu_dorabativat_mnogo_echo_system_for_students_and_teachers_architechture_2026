<?php
require_once 'models/Grade.php';
require_once 'models/Enrollment.php';

class GradeController {
    private $grade;
    private $enrollment;
    private $user_role;
    
    public function __construct($user_role = null) {
        $this->grade = new Grade();
        $this->enrollment = new Enrollment();
        $this->user_role = $user_role;
    }
    
    public function getByStudent($student_id) {
        $grades = $this->grade->getByStudent($student_id);
        $average = $this->grade->getAverageForStudent($student_id);
        
        $this->sendResponse(200, [
            'grades' => $grades,
            'statistics' => $average
        ]);
    }
    
    public function getByCourse($course_id) {
        $grades = $this->grade->getByCourse($course_id);
        $this->sendResponse(200, $grades);
    }
    
    public function create() {
        if ($this->user_role !== 'admin' && $this->user_role !== 'teacher') {
            $this->sendResponse(403, ['error' => 'Permission denied']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['enrollment_id']) || !isset($data['grade_value'])) {
            $this->sendResponse(400, ['error' => 'enrollment_id and grade_value are required']);
            return;
        }
        
        $data['grade_type'] = $data['grade_type'] ?? 'Exam';
        
        $gradeId = $this->grade->create($data);
        if ($gradeId) {
            $this->sendResponse(201, ['message' => 'Grade created successfully', 'id' => $gradeId]);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to create grade']);
        }
    }
    
    public function update($id) {
        if ($this->user_role !== 'admin' && $this->user_role !== 'teacher') {
            $this->sendResponse(403, ['error' => 'Permission denied']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['grade_value'])) {
            $this->sendResponse(400, ['error' => 'grade_value is required']);
            return;
        }
        
        if ($this->grade->update($id, $data['grade_value'])) {
            $this->sendResponse(200, ['message' => 'Grade updated successfully']);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to update grade']);
        }
    }
    
    public function delete($id) {
        if ($this->user_role !== 'admin') {
            $this->sendResponse(403, ['error' => 'Permission denied']);
            return;
        }
        
        if ($this->grade->delete($id)) {
            $this->sendResponse(200, ['message' => 'Grade deleted successfully']);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to delete grade']);
        }
    }
    
    private function sendResponse($status_code, $data) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
?>

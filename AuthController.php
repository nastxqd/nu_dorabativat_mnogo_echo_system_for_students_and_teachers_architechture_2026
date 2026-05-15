<?php
require_once 'models/User.php';
require_once 'utils/JwtHandler.php';

class AuthController {
    private $user;
    private $jwt;
    
    public function __construct() {
        $this->user = new User();
        $this->jwt = new JwtHandler();
    }
    
    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['username']) || !isset($data['password'])) {
            $this->sendResponse(400, ['error' => 'Username and password required']);
            return;
        }
        
        if ($this->user->authenticate($data['username'], $data['password'])) {
            $token = $this->jwt->generateToken($this->user->id, $this->user->username, $this->user->role);
            
            $this->sendResponse(200, [
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                    'role' => $this->user->role
                ]
            ]);
        } else {
            $this->sendResponse(401, ['error' => 'Invalid credentials']);
        }
    }
    
    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $required = ['username', 'password', 'email', 'role'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->sendResponse(400, ['error' => "Field '{$field}' is required"]);
                return;
            }
        }
        
        $userId = $this->user->create($data);
        if ($userId) {
            $this->sendResponse(201, ['message' => 'User created successfully', 'user_id' => $userId]);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to create user']);
        }
    }
    
    private function sendResponse($status_code, $data) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
?>
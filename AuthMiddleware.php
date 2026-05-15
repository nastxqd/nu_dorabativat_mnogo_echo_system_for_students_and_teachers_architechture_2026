<?php
require_once 'utils/JwtHandler.php';

class AuthMiddleware {
    private $jwt;
    
    public function __construct() {
        $this->jwt = new JwtHandler();
    }
    
    public function authenticate() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            $this->sendUnauthorized('No token provided');
            return null;
        }
        
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        
        $userData = $this->jwt->validateToken($token);
        
        if (!$userData) {
            $this->sendUnauthorized('Invalid or expired token');
            return null;
        }
        
        return $userData;
    }
    
    private function sendUnauthorized($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit();
    }
}
?>
<?php
class JwtHandler {
    private $secret_key = 'your-secret-key-here-change-it';
    private $algorithm = 'HS256';
    
    public function generateToken($user_id, $username, $role) {
        $issued_at = time();
        $expiration_time = $issued_at + (60 * 60 * 24); // 24 часа
        
        $payload = array(
            "iat" => $issued_at,
            "exp" => $expiration_time,
            "data" => array(
                "id" => $user_id,
                "username" => $username,
                "role" => $role
            )
        );
        
        return $this->encode($payload);
    }
    
    public function validateToken($token) {
        try {
            $decoded = $this->decode($token);
            if ($decoded->exp < time()) {
                return null;
            }
            return $decoded->data;
        } catch(Exception $e) {
            return null;
        }
    }
    
    private function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    private function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid token');
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])));
        
        $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $this->secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64UrlSignature !== $parts[2]) {
            throw new Exception('Invalid signature');
        }
        
        return $payload;
    }
}
?>
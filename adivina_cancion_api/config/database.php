<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Database {
    // 🔥 DATOS DE RAILWAY
    private $host = "switchyard.proxy.rlwy.net";
    private $db_name = "railway";
    private $username = "root";
    private $password = "ftczUqIvumPzeMQuzwqSYtbDwTslHlhs";
    private $port = 17643;

    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // 🔥 Conexión correcta a Railway con puerto y UTF-8
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error de conexión a la base de datos",
                "error" => $exception->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }
}

// Función auxiliar para enviar respuestas JSON
function sendResponse($success, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    
    $response = [
        "success" => $success,
        "message" => $message
    ];
    
    if ($data !== null) {
        $response["data"] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

// Función para validar token JWT (implementación simple)
function validateToken($token) {
    // Por ahora retornamos true, luego se implementará JWT real
    return true;
}
?>

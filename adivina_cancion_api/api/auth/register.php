<?php
require_once '../../config/database.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Método no permitido", null, 405);
}

// Obtener datos JSON
$input = file_get_contents("php://input");
$data = json_decode($input);

// Validar que llegaron datos
if (!$data) {
    sendResponse(false, "Datos JSON inválidos", null, 400);
}

// Validar datos requeridos
if (empty($data->username) || empty($data->email) || empty($data->password)) {
    sendResponse(false, "Usuario, email y contraseña son requeridos", null, 400);
}

// Validar formato de email
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "Email inválido", null, 400);
}

// Validar longitud de contraseña
if (strlen($data->password) < 6) {
    sendResponse(false, "La contraseña debe tener al menos 6 caracteres", null, 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendResponse(false, "Error de conexión a la base de datos", null, 500);
}

try {
    // Verificar si el usuario ya existe
    $checkQuery = "SELECT id FROM usuarios WHERE username = :username OR email = :email LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":username", $data->username, PDO::PARAM_STR);
    $checkStmt->bindParam(":email", $data->email, PDO::PARAM_STR);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        sendResponse(false, "El usuario o email ya existe", null, 409);
    }
    
    // Hashear contraseña con bcrypt
    $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
    
    // Insertar nuevo usuario
    $query = "INSERT INTO usuarios (username, email, password, fecha_registro) 
              VALUES (:username, :email, :password, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $data->username, PDO::PARAM_STR);
    $stmt->bindParam(":email", $data->email, PDO::PARAM_STR);
    $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        $userId = $db->lastInsertId();
        
        // Preparar respuesta exitosa
        $userData = [
            "id" => (int)$userId,
            "username" => $data->username,
            "email" => $data->email,
            "stats" => [
                "totalMatches" => 0,
                "bestScore" => 0,
                "totalPoints" => 0
            ]
        ];
        
        sendResponse(true, "Usuario registrado exitosamente", $userData, 201);
        
    } else {
        sendResponse(false, "Error al registrar usuario", null, 500);
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error en el servidor", ["error" => $e->getMessage()], 500);
}
?>
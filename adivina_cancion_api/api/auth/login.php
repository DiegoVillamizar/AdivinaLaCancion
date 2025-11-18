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
if (empty($data->username) || empty($data->password)) {
    sendResponse(false, "Usuario y contraseña son requeridos", null, 400);
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendResponse(false, "Error de conexión a la base de datos", null, 500);
}

try {
    // Buscar usuario por username o email
    $query = "SELECT id, username, email, password, total_partidas, mejor_puntuacion, puntos_totales 
              FROM usuarios 
              WHERE username = :username OR email = :username
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $data->username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar contraseña usando password_verify
        if (password_verify($data->password, $row['password'])) {
            
            // Actualizar último acceso
            $updateQuery = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(":id", $row['id'], PDO::PARAM_INT);
            $updateStmt->execute();
            
            // Preparar respuesta exitosa
            $userData = [
                "id" => (int)$row['id'],
                "username" => $row['username'],
                "email" => $row['email'],
                "stats" => [
                    "totalMatches" => (int)$row['total_partidas'],
                    "bestScore" => (int)$row['mejor_puntuacion'],
                    "totalPoints" => (int)$row['puntos_totales']
                ]
            ];
            
            sendResponse(true, "Login exitoso", $userData, 200);
            
        } else {
            sendResponse(false, "Contraseña incorrecta", null, 401);
        }
    } else {
        sendResponse(false, "Usuario no encontrado", null, 404);
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error en el servidor", ["error" => $e->getMessage()], 500);
}
?>
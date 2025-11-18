<?php
require_once '../../config/database.php';

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, "Método no permitido");
}

// Obtener parámetros opcionales
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;

$database = new Database();
$db = $database->getConnection();

try {
    // Obtener ranking global usando la vista
    $query = "SELECT id, username, puntuacion, total_partidas, puntos_totales, posicion 
              FROM ranking_global 
              LIMIT :limit";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $ranking = [];
    $usuarioEncontrado = false;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isCurrentUser = ($usuario_id && $row['id'] == $usuario_id);
        
        if ($isCurrentUser) {
            $usuarioEncontrado = true;
        }
        
        $ranking[] = [
            "id" => (int)$row['id'],
            "username" => $row['username'],
            "score" => (int)$row['puntuacion'],
            "totalPartidas" => (int)$row['total_partidas'],
            "totalPuntos" => (int)$row['puntos_totales'],
            "position" => (int)$row['posicion'],
            "isCurrentUser" => $isCurrentUser
        ];
    }
    
    // Si el usuario no está en el top, obtener su posición
    if ($usuario_id && !$usuarioEncontrado) {
        $userQuery = "SELECT 
                        u.id,
                        u.username,
                        u.mejor_puntuacion as puntuacion,
                        u.total_partidas,
                        u.puntos_totales,
                        (SELECT COUNT(*) + 1 
                         FROM usuarios u2 
                         WHERE u2.mejor_puntuacion > u.mejor_puntuacion 
                         AND u2.total_partidas > 0) as posicion
                      FROM usuarios u
                      WHERE u.id = :usuario_id AND u.total_partidas > 0";
        
        $userStmt = $db->prepare($userQuery);
        $userStmt->bindParam(":usuario_id", $usuario_id);
        $userStmt->execute();
        
        if ($userStmt->rowCount() > 0) {
            $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            $ranking[] = [
                "id" => (int)$userRow['id'],
                "username" => $userRow['username'],
                "score" => (int)$userRow['puntuacion'],
                "totalPartidas" => (int)$userRow['total_partidas'],
                "totalPuntos" => (int)$userRow['puntos_totales'],
                "position" => (int)$userRow['posicion'],
                "isCurrentUser" => true
            ];
        }
    }
    
    sendResponse(true, "Ranking obtenido exitosamente", $ranking);
    
} catch (PDOException $e) {
    sendResponse(false, "Error en el servidor: " . $e->getMessage());
}
?>
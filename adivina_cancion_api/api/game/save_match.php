<?php
require_once '../../config/database.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Método no permitido");
}

// Obtener datos JSON
$data = json_decode(file_get_contents("php://input"));

// Validar datos requeridos
if (empty($data->usuario_id) || empty($data->nivel) || !isset($data->puntuacion) || 
    !isset($data->total_rondas) || !isset($data->aciertos) || !isset($data->errores)) {
    sendResponse(false, "Faltan datos requeridos");
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    
    // Usar el procedimiento almacenado para registrar la partida
    $query = "CALL registrar_partida(:usuario_id, :nivel, :puntuacion, :total_rondas, :aciertos, :errores)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":usuario_id", $data->usuario_id);
    $stmt->bindParam(":nivel", $data->nivel);
    $stmt->bindParam(":puntuacion", $data->puntuacion);
    $stmt->bindParam(":total_rondas", $data->total_rondas);
    $stmt->bindParam(":aciertos", $data->aciertos);
    $stmt->bindParam(":errores", $data->errores);
    
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $partidaId = $result['partida_id'];
    
    // Cerrar el cursor para la siguiente consulta
    $stmt->closeCursor();
    
    // Obtener estadísticas actualizadas del usuario
    $statsQuery = "SELECT total_partidas, mejor_puntuacion, puntos_totales 
                   FROM usuarios 
                   WHERE id = :usuario_id";
    $statsStmt = $db->prepare($statsQuery);
    $statsStmt->bindParam(":usuario_id", $data->usuario_id);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $db->commit();
    
    $responseData = [
        "partida_id" => $partidaId,
        "stats" => [
            "totalMatches" => (int)$stats['total_partidas'],
            "bestScore" => (int)$stats['mejor_puntuacion'],
            "totalPoints" => (int)$stats['puntos_totales']
        ]
    ];
    
    sendResponse(true, "Partida guardada exitosamente", $responseData);
    
} catch (PDOException $e) {
    $db->rollBack();
    sendResponse(false, "Error al guardar partida: " . $e->getMessage());
}
?>
<?php
require_once '../../config/database.php';

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, "Método no permitido");
}

// Obtener parámetros
$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : null;
$cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 5;

// Validar nivel
$nivelesValidos = ['Fácil', 'Medio', 'Difícil'];
if (!in_array($nivel, $nivelesValidos)) {
    sendResponse(false, "Nivel inválido. Debe ser: Fácil, Medio o Difícil");
}

$database = new Database();
$db = $database->getConnection();

try {
    // Obtener canciones aleatorias del nivel especificado
    $query = "SELECT id, titulo, artista, genero, duracion_fragmento, ruta_audio 
              FROM canciones 
              WHERE nivel = :nivel AND activa = 1 
              ORDER BY RAND() 
              LIMIT :cantidad";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":nivel", $nivel);
    $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
    $stmt->execute();
    
    $canciones = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Obtener opciones para esta canción
        $opcionesQuery = "SELECT opcion_texto, es_correcta 
                         FROM opciones_respuesta 
                         WHERE cancion_id = :cancion_id 
                         ORDER BY RAND()";
        
        $opcionesStmt = $db->prepare($opcionesQuery);
        $opcionesStmt->bindParam(":cancion_id", $row['id']);
        $opcionesStmt->execute();
        
        $opciones = [];
        $respuestaCorrecta = '';
        
        while ($opcion = $opcionesStmt->fetch(PDO::FETCH_ASSOC)) {
            $opciones[] = $opcion['opcion_texto'];
            if ($opcion['es_correcta'] == 1) {
                $respuestaCorrecta = $opcion['opcion_texto'];
            }
        }
        
        $canciones[] = [
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "artista" => $row['artista'],
            "genero" => $row['genero'],
            "duracionFragmento" => (int)$row['duracion_fragmento'],
            "rutaAudio" => $row['ruta_audio'],
            "opciones" => $opciones,
            "respuestaCorrecta" => $respuestaCorrecta
        ];
    }
    
    if (count($canciones) > 0) {
        sendResponse(true, "Canciones obtenidas exitosamente", $canciones);
    } else {
        sendResponse(false, "No hay canciones disponibles para este nivel");
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error en el servidor: " . $e->getMessage());
}
?>
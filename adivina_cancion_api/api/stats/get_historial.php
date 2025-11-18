<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, "Método no permitido", null, 405);
}

$usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;

if (!$usuario_id) {
    sendResponse(false, "Usuario ID requerido", null, 400);
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT 
                id,
                nivel,
                puntuacion,
                total_rondas,
                aciertos,
                errores,
                fecha_partida
              FROM partidas
              WHERE usuario_id = :usuario_id
              ORDER BY fecha_partida DESC
              LIMIT 50";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":usuario_id", $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $partidas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $partidas[] = [
            "id" => (int)$row['id'],
            "nivel" => $row['nivel'],
            "puntuacion" => (int)$row['puntuacion'],
            "total_rondas" => (int)$row['total_rondas'],
            "aciertos" => (int)$row['aciertos'],
            "errores" => (int)$row['errores'],
            "fecha_partida" => $row['fecha_partida'],
        ];
    }
    
    sendResponse(true, "Historial obtenido", $partidas, 200);
    
} catch (PDOException $e) {
    sendResponse(false, "Error en el servidor", ["error" => $e->getMessage()], 500);
}
?>
```

---

## ✅ Verificación:

### **Archivos que debes tener:**
```
src/
└── screens/
    ├── SplashScreen.js       ✅
    ├── LoginScreen.js        ✅
    ├── HomeScreen.js         ✅
    ├── LevelScreen.js        ✅
    ├── GameScreen.js         ✅
    ├── ResultsScreen.js      ✅
    ├── HistorialScreen.js    ← CREAR ESTE
    └── AjustesScreen.js      ← CREAR ESTE
```
```
htdocs/adivina_cancion_api/api/stats/
├── get_ranking.php      ✅
└── get_historial.php    ← CREAR ESTE
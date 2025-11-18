<?php
require_once 'config/database.php';

echo "<h2>Prueba de Conexión a Base de Datos</h2>";

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos!</p>";
    
    // Probar query
    try {
        $query = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        
        echo "<p>✅ Total de usuarios en la BD: <strong>" . $result['total'] . "</strong></p>";
        
        // Mostrar usuarios
        $query2 = "SELECT id, username, email, fecha_registro FROM usuarios ORDER BY id DESC LIMIT 5";
        $stmt2 = $db->prepare($query2);
        $stmt2->execute();
        
        echo "<h3>Últimos usuarios registrados:</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Fecha Registro</th></tr>";
        
        while ($row = $stmt2->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['fecha_registro'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error en query: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Error de conexión</p>";
}
?>
```

---

## 🧪 Pasos para probar:

### **1. Verifica la conexión:**
Abre en tu navegador:
```
http://localhost/adivina_cancion_api/test_connection.php
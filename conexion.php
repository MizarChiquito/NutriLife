<?php
$host = '127.0.0.1';
$port = '3307'; // Cambia a 3307 si XAMPP muestra ese puerto
$dbname = 'foodre_db';
$username = 'root';
$password = ''; // XAMPP usa usuario root sin contraseña

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2 style='color:green;'>✅ Conexión exitosa con la base de datos foodre_db</h2>";
} catch (PDOException $e) {
    echo "<h1 style='color:red;'>❌ Error de Conexión a la Base de Datos</h1>";
    echo "<p>Detalles: " . $e->getMessage() . "</p>";
}
?>

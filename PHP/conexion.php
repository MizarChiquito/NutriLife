<?php
// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la Base de Datos (MySQL)
$host = "localhost";
$port = "3306";
$db   = "foodre_db";        
$user = "root";
$pass = "";            

// Variable $pdo para usar en otros scripts.
$pdo = null;

try {
    // Crear conexión PDO para MySQL
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);

    // Configurar PDO para lanzar excepciones si ocurre un error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si falla la conexión, muestra un error y detiene la ejecución.
    die("<h1>❌ Error de Conexión a la Base de Datos</h1>
         <p>Verifica tu configuración de MySQL o que tu servidor esté activo: " . $e->getMessage() . "</p>");
}
// Si la conexión es exitosa, el script termina aquí y la variable $pdo ya está definida.
?>
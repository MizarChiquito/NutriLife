<?php
// procesar_login.php

// 1. Iniciar la Sesión y Conexión
global $pdo;
session_start();
require_once 'conexion.php';

// 2. Seguridad inicial: Verificar el metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $msg = urlencode("Acceso no autorizado.");
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}

// 3. OBTENCIÓN Y SANITIZACIÓN DE DATOS (Bloque faltante)
$email = trim(htmlspecialchars($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// 4. VALIDACIÓN DE CAMPOS VACÍOS (Bloque faltante)
if (empty($email) || empty($password)) {
    $msg = urlencode("Debe ingresar su correo electrónico y su contraseña.");
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}

// 5. Consultar Usuario y Rol
// NOTA: Se usan comillas simples (') para evitar errores de sintaxis con los alias SQL u y r
$sql_user = 'SELECT u.id, u.password_hash, r.name AS role_name, u.first_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = :email';

$stmt = $pdo->prepare($sql_user);
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 6. Validación de Credenciales (verifica si el hash coincide)
if (!$user || !password_verify($password, $user['password_hash'])) {
    $msg = urlencode("Credenciales incorrectas.");
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}

// 7. Configuración de Sesión (¡ÉXITO!)
$_SESSION['LAST_ACTIVITY'] = time();
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['first_name'];
$_SESSION['role'] = $user['role_name'];

// 8. Redirección Basada en Rol
switch ($user['role_name']) {
    case 'Nutriologo':
        header("Location: nutriologo_dashboard.php");
        break;
    case 'Paciente':
        header("Location: paciente_dashboard.php");
        break;
    case 'Administrador':
        header("Location: admin_dashboard.php");
        break;
    default:
        session_unset(); session_destroy();
        $msg = urlencode("Rol no válido.");
        header("Location: login.html?status=error&msg=" . $msg);
        break;
}
exit();
?>
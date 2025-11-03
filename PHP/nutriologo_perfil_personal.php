<?php
// nutriologo_perfil_personal.php
// (Controlador que carga los datos del nutriólogo y muestra la vista)

global $pdo;
session_start();
require_once 'conexion.php';

// Variables de estado
$user_data = null;
$msg = '';
$status = '';

// 1. Verificar Autenticación y Rol
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Nutriologo') {
    $msg_url = urlencode("Acceso denegado. Inicie sesión como Nutriólogo.");
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg_url);
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener mensajes de la URL (éxito o error de la actualización)
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars(urldecode($_GET['msg']));
    $status = $_GET['status'] ?? 'info';
}

try {
    // 2. Obtener Datos Actuales del Nutriólogo
    $sql = 'SELECT first_name, last_name, email FROM users WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        $msg = "Error: Usuario no encontrado.";
        $status = 'error';
    }

} catch (PDOException $e) {
    $msg = "Error de base de datos al cargar la información.";
    $status = 'error';
}

// 3. Incluir la Vista (El HTML/JS)
require 'nutriologo_perfil_personal_vista.php';
?>
<?php
// nutriologo_actualizar_personal.php

session_start();
require_once 'conexion.php';
global $pdo;

// 1. Verificar Autenticación, Rol y Método POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Nutriologo' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $msg_url = urlencode("Acceso no autorizado.");
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg_url);
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Recepción y Sanitización de Datos
$first_name = filter_var(trim($_POST['first_name'] ?? ''), FILTER_SANITIZE_STRING);
$last_name = filter_var(trim($_POST['last_name'] ?? ''), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

// 3. Validación
if (empty($first_name) || empty($last_name) || !$email) {
    $msg = urlencode("Datos inválidos o faltantes.");
    // Redirige de vuelta al formulario del nutriólogo
    header("Location: nutriologo_perfil_personal.php?status=error&msg=" . $msg);
    exit();
}

try {
    // 4. Ejecutar la Sentencia UPDATE
    $sql = 'UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id';
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    $stmt->execute();

    // 5. Redirección de Éxito
    $msg = urlencode("¡Datos actualizados con éxito!");
    header("Location: nutriologo_perfil_personal.php?status=success&msg=" . $msg);
    exit();

} catch (PDOException $e) {
    $msg = urlencode("Error al actualizar datos.");
    if ($e->getCode() === '23000') { // Error de email duplicado
        $msg = urlencode("Error: Este correo electrónico ya está en uso.");
    }
    header("Location: nutriologo_perfil_personal.php?status=error&msg=" . $msg);
    exit();
}
?>
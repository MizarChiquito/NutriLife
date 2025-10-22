<?php
// actualizar_datos.php

session_start();
require_once 'conexion.php';

// 1. Verificar Autenticación y Método POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../HTML/GENERALES/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Recepción y Sanitización de Datos
$first_name = filter_var(trim($_POST['first_name'] ?? ''), FILTER_SANITIZE_STRING);
$last_name = filter_var(trim($_POST['last_name'] ?? ''), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

// 3. Validación Básica
if (empty($first_name) || empty($last_name) || !$email) {
    $msg = urlencode("Todos los campos son obligatorios y el email debe ser válido.");
    header("Location: modificar_datos_paciente.php?status=error&msg=" . $msg);
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

    // 5. Redirección de Éxito al controlador (para recargar los datos y mostrar el mensaje)
    $msg = urlencode("¡Datos actualizados con éxito!");
    header("Location: modificar_datos_paciente.php?status=success&msg=" . $msg);
    exit();

} catch (PDOException $e) {
    $msg = urlencode("Error al actualizar datos. Inténtelo de nuevo.");

    // Si el email ya está en uso (Código 23000 de MySQL)
    if ($e->getCode() === '23000') {
        $msg = urlencode("Error: Este correo electrónico ya está en uso por otra cuenta.");
    }

    header("Location: modificar_datos_paciente.php?status=error&msg=" . $msg);
    exit();
}
?>
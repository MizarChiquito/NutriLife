<?php
// modificar_datos_controlador.php

global $pdo;
session_start();
require_once 'conexion.php'; // Asegúrate de que este archivo define $pdo

// Variables de estado
$user_data = null;
$msg = '';
$status = '';

// 1. Verificar Autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: ../HTML/GENERALES/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener mensajes de la URL (éxito o error de la actualización)
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars(urldecode($_GET['msg']));
    $status = $_GET['status'] ?? 'info';
}

try {
    // 2. Obtener Datos Actuales del Usuario
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
// NOTA: El archivo 'modificar_datos_vista.html' DEBE ser renombrado a 'modificar_datos_vista.php'
// para que las etiquetas <?php
require 'modificar_datos_vista.php';
?>
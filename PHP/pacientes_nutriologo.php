<?php
session_start();
require_once "conexion.php";
require_once "check_session.php";

header('Content-Type: application/json');

// Verificar rol
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Nutriologo') {
    echo json_encode([]);
    exit;
}

$nutriologo_id = $_SESSION['user_id'] ?? null;
if (!$nutriologo_id) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id AS paciente_id, first_name, last_name, weight, height
        FROM users
        WHERE nutriologo_id = :nid
        ORDER BY first_name ASC
    ");
    $stmt->execute([':nid' => $nutriologo_id]);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($pacientes);

} catch(Exception $e) {
    echo json_encode([]);
}
?>




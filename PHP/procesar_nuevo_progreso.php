<?php
// procesar_nuevo_progreso.php

require_once 'check_session.php';
require_once 'conexion.php';
global $pdo;

// 1. Verificar Rol y Método
if ($_SESSION['role'] !== 'Nutriologo') {
    die("Acceso denegado. Solo Nutriólogos pueden registrar progreso.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}

// 2. Recepción y Sanitización de Datos
$paciente_id = intval($_POST['paciente_id'] ?? 0);
$fecha = trim($_POST['fecha'] ?? '');
$nuevo_peso = floatval($_POST['nuevoPeso'] ?? 0);
$nueva_altura = floatval($_POST['nuevaAltura'] ?? 0);

// 3. Validación de campos
if ($paciente_id <= 0 || empty($fecha) || $nuevo_peso < 30 || $nuevo_peso > 300 || $nueva_altura < 0.50 || $nueva_altura > 3.00) {
    die("<h1>❌ Error de Validación</h1><p>Datos faltantes o fuera de rango.</p>");
}

// 4. Iniciar Transacción Atómica
$pdo->beginTransaction();

try {
    // 4.1. INSERCIÓN del nuevo progreso (CREATE)
    $sql_insert_progress = "INSERT INTO progress (user_id, measurement_date, weight, height) 
                            VALUES (:user_id, :fecha, :weight, :height)";
    $stmt_insert = $pdo->prepare($sql_insert_progress);

    $stmt_insert->bindParam(':user_id', $paciente_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':fecha', $fecha);
    $stmt_insert->bindParam(':weight', $nuevo_peso);
    $stmt_insert->bindParam(':height', $nueva_altura);
    $stmt_insert->execute();

    // 4.2. ACTUALIZACIÓN de peso y altura en la tabla principal de users (UPDATE)
    $sql_update_user = "UPDATE users SET weight = :weight, height = :height WHERE id = :user_id";
    $stmt_update = $pdo->prepare($sql_update_user);

    $stmt_update->bindParam(':weight', $nuevo_peso);
    $stmt_update->bindParam(':height', $nueva_altura);
    $stmt_update->bindParam(':user_id', $paciente_id, PDO::PARAM_INT);
    $stmt_update->execute();

    // 4.3. Confirmar la transacción
    $pdo->commit();

    // Redirección con éxito (Ej. Volver a la lista de pacientes)
    $msg = urlencode("✅ Progreso registrado y datos del paciente actualizados.");
    header("Location: ../HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=success&msg=" . $msg);
    exit();

} catch (PDOException $e) {
    // 4.4. Revertir Transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $msg = urlencode("❌ Error al registrar progreso: " . $e->getMessage());
    header("Location: ../HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=error&msg=" . $msg);
    exit();
}
?>
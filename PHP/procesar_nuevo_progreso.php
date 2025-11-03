<?php
// procesar_nuevo_progreso.php

require_once "conexion.php";
require_once "check_session.php";

global $pdo;

// 1️⃣ Verificar sesión y rol
if ($_SESSION['role'] !== 'Nutriologo') {
    die("<h1>⚠️ Acceso denegado</h1><p>No se ha iniciado sesión como nutriólogo.</p>");
}
$nutriologo_id = $_SESSION['user_id'];

// 2️⃣ Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<h1>❌ Error</h1><p>Acceso no autorizado.</p>");
}

// 3️⃣ Recibir y sanear datos del formulario
$paciente_id   = intval($_POST['paciente_id'] ?? 0);
$nuevo_peso    = floatval($_POST['nuevoPeso'] ?? 0);
$nueva_altura  = floatval($_POST['nuevaAltura'] ?? 0);
$fecha         = trim($_POST['fecha'] ?? '');

// 3.1 Validar campos obligatorios
if ($paciente_id <= 0 || $nuevo_peso <= 0 || $nueva_altura <= 0 || empty($fecha)) {
    die("<h1>❌ Error</h1><p>Todos los campos son obligatorios.</p>");
}

// 3.2 Validar rangos
if ($nuevo_peso < 30 || $nuevo_peso > 300) {
    die("<h1>❌ Error</h1><p>Peso fuera del rango permitido (30-300 kg).</p>");
}
if ($nueva_altura < 0.5 || $nueva_altura > 3.0) {
    die("<h1>❌ Error</h1><p>Altura fuera del rango permitido (0.5-3.0 m).</p>");
}

// 3.3 Validar formato de fecha YYYY-MM-DD
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    die("<h1>❌ Error</h1><p>Formato de fecha inválido. Use YYYY-MM-DD.</p>");
}

// 4️⃣ Verificar que el paciente exista y pertenezca al nutriólogo
$stmt = $pdo->prepare("SELECT id, first_name, last_name, weight AS peso_actual, height 
                       FROM users 
                       WHERE id = :pid AND nutriologo_id = :nid");
$stmt->execute([':pid' => $paciente_id, ':nid' => $nutriologo_id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("<h1>❌ Error</h1><p>Paciente no encontrado o no pertenece al nutriólogo.</p>");
}

// 5️⃣ Verificar último progreso previo (si existe)
$stmtLast = $pdo->prepare("SELECT peso FROM progresos 
                           WHERE paciente_id = :pid 
                           ORDER BY fecha DESC, id DESC LIMIT 1");
$stmtLast->execute([':pid' => $paciente_id]);
$ultimo = $stmtLast->fetch(PDO::FETCH_ASSOC);

$peso_referencia = $ultimo ? floatval($ultimo['peso']) : floatval($paciente['peso_actual']);

// 6️⃣ Insertar nuevo progreso
$stmtInsert = $pdo->prepare("INSERT INTO progresos (paciente_id, peso, altura, fecha, registrado_por)
                             VALUES (:pid, :peso, :altura, :fecha, :nutriologo_id)");
$stmtInsert->execute([
    ':pid' => $paciente_id,
    ':peso' => $nuevo_peso,
    ':altura' => $nueva_altura,
    ':fecha' => $fecha,
    ':nutriologo_id' => $nutriologo_id
]);

// 7️⃣ Calcular diferencia de peso
$diff = $nuevo_peso - $peso_referencia;
$diff_rounded = round($diff, 2);
$signo = $diff_rounded > 0 ? "+" : ($diff_rounded < 0 ? "−" : "0");
$color = ($diff_rounded > 0) ? "red" : (($diff_rounded < 0) ? "green" : "black");

// 8️⃣ Actualizar peso y altura actuales en tabla users
$stmt_update = $pdo->prepare("UPDATE users SET weight = :peso, height = :altura WHERE id = :pid");
$stmt_update->execute([
    ':peso' => $nuevo_peso,
    ':altura' => $nueva_altura,
    ':pid' => $paciente_id
]);

// 9️⃣ Preparar mensaje para notificación JS
$mensaje = urlencode("Progreso registrado para {$paciente['first_name']} {$paciente['last_name']}. "
    . "Cambio: <span style='color:{$color}'>{$signo}{$diff_rounded} kg</span> (ref: {$peso_referencia} kg).");

//  🔟 Redirigir a mis_pacientes.html con mensaje
header("Location: ../HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=success&msg={$mensaje}");
exit();
?>

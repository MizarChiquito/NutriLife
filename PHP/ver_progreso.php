<?php
// ver_progreso.php

require_once 'check_session.php';
require_once 'conexion.php';
global $pdo;

$user_role = $_SESSION['role'];
$session_user_id = $_SESSION['user_id'];
$paciente_id = $session_user_id; // Por defecto, ve su propio progreso

// 1. Determinar el Paciente ID a consultar
if ($user_role === 'Nutriologo') {
    // Si es Nutriólogo, el ID del paciente viene por GET (ej. desde la lista)
    $paciente_id = intval($_GET['paciente_id'] ?? $session_user_id);

    // [Pendiente de implementar: Verificación de que el Nutriólogo está asignado al paciente]
    // Por ahora, asumimos que puede ver el progreso si tiene el ID.

} elseif ($user_role !== 'Paciente') {
    // Solo Nutriólogo y Paciente tienen acceso
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . urlencode("Acceso no autorizado."));
    exit();
}

// 2. Obtener Nombre del Paciente para el Título
$sql_paciente = "SELECT first_name, last_name FROM users WHERE id = :id";
$stmt_paciente = $pdo->prepare($sql_paciente);
$stmt_paciente->bindParam(':id', $paciente_id, PDO::PARAM_INT);
$stmt_paciente->execute();
$paciente_data = $stmt_paciente->fetch(PDO::FETCH_ASSOC);

if (!$paciente_data) {
    die("Paciente no encontrado.");
}
$paciente_nombre_completo = htmlspecialchars($paciente_data['first_name'] . ' ' . $paciente_data['last_name']);

// 3. Obtener Historial de Progreso (Más reciente primero)
$sql_progress = "SELECT measurement_date, weight, height FROM progress 
                 WHERE user_id = :user_id 
                 ORDER BY measurement_date DESC"; // El más reciente primero
$stmt_progress = $pdo->prepare($sql_progress);
$stmt_progress->bindParam(':user_id', $paciente_id, PDO::PARAM_INT);
$stmt_progress->execute();
$progress_entries = $stmt_progress->fetchAll(PDO::FETCH_ASSOC);

// 4. Procesar y Renderizar la tabla
$html_rows = '';
$peso_anterior = null; // Para calcular la diferencia

if (empty($progress_entries)) {
    $html_rows = '<tr><td colspan="4" style="text-align: center;">No hay historial de progreso registrado.</td></tr>';
} else {
    foreach ($progress_entries as $entry) {
        $peso_actual = floatval($entry['weight']);
        $altura_actual = floatval($entry['height']);
        $fecha_formato = date('d/m/Y', strtotime($entry['measurement_date']));

        $cambio_peso = 'N/A';
        $style_cambio = '';

        if ($peso_anterior !== null) {
            $diferencia = $peso_actual - $peso_anterior;
            $signo = ($diferencia > 0) ? '+' : '';
            $cambio_peso = $signo . number_format($diferencia, 2) . ' kg';
            $style_cambio = ($diferencia > 0) ? 'style="color: red; font-weight: bold;"' : 'style="color: green; font-weight: bold;"';
        }

        $html_rows .= sprintf(
            '<tr><td>%s</td><td>%.2f kg</td><td>%.2f m</td><td %s>%s</td></tr>',
            $fecha_formato,
            $peso_actual,
            $altura_actual,
            $style_cambio,
            $cambio_peso
        );

        $peso_anterior = $peso_actual; // El peso actual se convierte en el anterior para la siguiente fila
    }
    // NOTA: El cálculo de cambio de peso en la BD se hace del registro anterior. Como se itera en orden DESC, el último registro es el más antiguo.
    // El peso anterior del bucle es, de hecho, el peso *posterior* en el tiempo.
}

// 5. Cargar y Reemplazar Marcadores en el HTML
$html_content = file_get_contents('../HTML/GENERALES/historial_progreso.html');

// Título
$html_content = str_replace('[Nombre]', $paciente_nombre_completo, $html_content);

// Botón de nuevo progreso (solo visible para Nutriólogo y apuntando al paciente correcto)
if ($user_role === 'Nutriologo') {
    $html_content = str_replace(
        '<a href="#" id="btn-nuevo-progreso" style="display: none;',
        sprintf('<a href="../NUTRIOLOGO/nutriologo_nuevo_progreso.html?paciente_id=%d" id="btn-nuevo-progreso" style="display: inline-block;', $paciente_id),
        $html_content
    );
}
// Ocultar botón de nuevo progreso para Paciente
$html_content = str_replace('id="btn-nuevo-progreso"', 'id="btn-nuevo-progreso" style="display: none;"', $html_content);


// Contenido de la tabla
$html_content = str_replace('<tr><td colspan="4" style="text-align: center;">Cargando historial...</td></tr>', $html_rows, $html_content);

// 6. Mostrar el HTML
echo $html_content;
?>
<?php
// cargar_pacientes.php
require_once "conexion.php";
require_once "check_session.php";

global $pdo;

// Verificar sesión y rol
if ($_SESSION['role'] !== 'Nutriologo') {
    die("<h1>⚠️ Acceso denegado</h1><p>No tienes permiso para acceder a esta sección.</p>");
}

$nutriologo_id = $_SESSION['user_id'];

// Consultar todos los pacientes del nutriólogo
$stmt = $pdo->prepare("
    SELECT 
        u.id AS paciente_id,
        u.first_name,
        u.last_name,
        u.weight,
        u.height
    FROM users u
    WHERE u.nutriologo_id = :nid
    ORDER BY u.first_name ASC
");
$stmt->execute([':nid' => $nutriologo_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$pacientes) {
    echo "<tr><td colspan='6' style='text-align:center;'>No tienes pacientes registrados.</td></tr>";
    exit();
}

// Por cada paciente, calcular el progreso reciente
foreach ($pacientes as $p) {
    $paciente_id = $p['paciente_id'];
    $peso_actual = floatval($p['weight']);

    // Último progreso registrado
    $stmtUltimo = $pdo->prepare("
        SELECT peso, fecha 
        FROM progresos 
        WHERE paciente_id = :pid 
        ORDER BY fecha DESC, id DESC 
        LIMIT 1
    ");
    $stmtUltimo->execute([':pid' => $paciente_id]);
    $ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);

    // Progreso anterior al último
    $stmtAnterior = $pdo->prepare("
        SELECT peso 
        FROM progresos 
        WHERE paciente_id = :pid 
        ORDER BY fecha DESC, id DESC 
        LIMIT 1 OFFSET 1
    ");
    $stmtAnterior->execute([':pid' => $paciente_id]);
    $anterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);

    // Determinar referencia
    if ($ultimo && $anterior) {
        $peso_ref = floatval($anterior['peso']);
        $fecha_ref = $ultimo['fecha'];
    } elseif ($ultimo) {
        $peso_ref = $peso_actual;
        $fecha_ref = $ultimo['fecha'];
    } else {
        $peso_ref = $peso_actual;
        $fecha_ref = "—";
    }

    // Diferencia y color
    $diff = $peso_actual - $peso_ref;
    $diff_rounded = round($diff, 2);
    $color = "black"; $signo = "";

    if ($diff_rounded > 0.1) { $color = "red"; $signo = "+"; }
    elseif ($diff_rounded < -0.1) { $color = "green"; $signo = ""; }

    $texto_progreso = ($fecha_ref === "—")
        ? "<span style='color:gray;'>Sin registros</span>"
        : "<span style='color:{$color};'>{$signo}{$diff_rounded} kg</span> ({$fecha_ref})";

    // Mostrar fila
    echo "<tr>
            <td>{$p['first_name']}</td>
            <td>{$p['last_name']}</td>
            <td>{$p['weight']} kg</td>
            <td>{$p['height']} m</td>
            <td>{$texto_progreso}</td>
            <td><a href='nutriologo_nuevo_progreso.html?paciente_id={$paciente_id}' class='accion'>➕ Nuevo Progreso</a></td>
          </tr>";
}
?>

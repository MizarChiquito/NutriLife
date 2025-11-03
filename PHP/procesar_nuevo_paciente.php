<?php
require_once 'conexion.php';
require_once 'check_session.php'; // Esto valida sesión y rol

// Solo nutriólogos pueden registrar pacientes
if ($_SESSION['role'] !== 'Nutriologo') {
    die("<h1>⚠️ Acceso denegado</h1><p>Solo los nutriólogos pueden registrar pacientes.</p>");
}

$nutriologo_id = $_SESSION['user_id']; // ID del nutriólogo actual
global $pdo;

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}

// Recibir y sanear datos
$primerNombre = trim(htmlspecialchars($_POST['primerNombre'] ?? ''));
$apellido     = trim(htmlspecialchars($_POST['apellido'] ?? ''));
$email        = trim(htmlspecialchars($_POST['email'] ?? ''));
$password     = $_POST['password'] ?? '';
$rol_nombre   = strtolower(trim($_POST['rol'] ?? ''));
$peso         = floatval($_POST['peso'] ?? 0);
$altura       = floatval($_POST['altura'] ?? 0);

// Validaciones
if (empty($primerNombre) || empty($apellido) || empty($email) || empty($password) || $rol_nombre !== 'paciente') {
    die("<h1>❌ Error</h1><p>Datos incompletos o rol incorrecto.</p>");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("<h1>❌ Error</h1><p>Correo inválido.</p>");
}
if (strlen($password) < 8 || strlen($password) > 12) {
    die("<h1>❌ Error</h1><p>Contraseña debe tener entre 8 y 12 caracteres.</p>");
}
if ($peso < 30 || $peso > 300) {
    die("<h1>❌ Error</h1><p>Peso fuera del rango (30-300 kg).</p>");
}
if ($altura < 0.5 || $altura > 3.0) {
    die("<h1>❌ Error</h1><p>Altura fuera del rango (0.5-3.0 m).</p>");
}

// Comprobar si el email existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
if ($stmt->rowCount() > 0) {
    die("<h1>❌ Error</h1><p>El correo ya está registrado.</p>");
}

// Obtener rol 'Paciente'
$stmt_rol = $pdo->prepare("SELECT id FROM roles WHERE LOWER(name) = 'paciente'");
$stmt_rol->execute();
$role = $stmt_rol->fetch(PDO::FETCH_ASSOC);
if (!$role) die("<h1>❌ Error</h1><p>El rol 'paciente' no existe.</p>");
$role_id = $role['id'];

// Hashear contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar paciente con nutriólogo asignado
$sql_insert = "INSERT INTO users 
               (first_name, last_name, email, password_hash, role_id, weight, height, nutriologo_id)
               VALUES 
               (:nombre, :apellido, :email, :pass, :rol, :peso, :altura, :nutriologo_id)";
$stmt_insert = $pdo->prepare($sql_insert);

try {
    $stmt_insert->execute([
        ':nombre' => $primerNombre,
        ':apellido' => $apellido,
        ':email' => $email,
        ':pass' => $password_hash,
        ':rol' => $role_id,
        ':peso' => $peso,
        ':altura' => $altura,
        ':nutriologo_id' => $nutriologo_id
    ]);

    $mensaje = urlencode("Paciente {$primerNombre} {$apellido} registrado correctamente.");
    header("Location: /Nutrilife/HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=success&msg={$mensaje}");
    exit();

} catch (PDOException $e) {
    $mensaje_error = urlencode("Error al registrar: " . $e->getMessage());
    header("Location: /Nutrilife/HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=error&msg={$mensaje_error}");
    exit();
}
?>

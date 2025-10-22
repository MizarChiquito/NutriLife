<?php

// 1. INCLUIR CONEXIÓN A MySQL (PDO)

require_once 'conexion.php';

global $pdo;

// 2. RECEPCIÓN, VALIDACIÓN Y SANITIZACIÓN DE DATOS

// 2.1 Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}

// 2.2 Obtener y sanear las entradas
$primerNombre = trim(htmlspecialchars($_POST['primerNombre'] ?? ''));
$apellido     = trim(htmlspecialchars($_POST['apellido'] ?? ''));
$email        = trim(htmlspecialchars($_POST['email'] ?? ''));
$password     = $_POST['password'] ?? '';

$rol_nombre   = trim($_POST['rol'] ?? '');

$peso         = $_POST['peso'] ?? '';
$altura       = $_POST['altura'] ?? '';

// 2.3 Validación de campos obligatorios
if (empty($primerNombre) || empty($apellido) || empty($email) || empty($password) || empty($peso) || empty($altura) || $rol_nombre !== 'Paciente') {
    die("<h1>❌ Error de Validación</h1><p>Datos faltantes o error de rol. Solo puede registrar Pacientes</p>");
}

// 2.4 Validación de Email (Formato)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("<h1>❌ Error de Validación</h1><p>El formato del correo electrónico es incorrecto.</p>");
}

// 2.5 Validación de Contraseña (Longitud y Requisitos Mínimos)
if (strlen($password) < 8 || strlen($password) > 12) {
    die("<h1>❌ Error de Validación</h1><p>La contraseña debe tener entre 8 y 12 caracteres.</p>");
}

// [NUEVA VALIDACIÓN: RANGOS DE PESO/ALTURA]
$peso_f = floatval($peso);
$altura_f = floatval($altura);

if ($peso_f < 30 || $peso_f > 300) {
    die("<h1>❌ Error de Validación</h1><p>El peso ingresado está fuera del rango (30-300 kg).</p>");
}
if ($altura_f < 0.5 || $altura_f > 3.0) {
    die("<h1>❌ Error de Validación</h1><p>La altura ingresada está fuera del rango (0.50-3.00 m).</p>");
}
// Las variables $peso_f y $altura_f ya están listas para la inserción
$peso = $peso_f;
$altura = $altura_f;

// 3. VERIFICAR UNICIDAD DEL EMAIL

// Aquí usamos $pdo. Si no está definido, el script falla.
$sql_check_email = "SELECT id FROM users WHERE email = :email";
$stmt_check_email = $pdo->prepare($sql_check_email);
$stmt_check_email->bindParam(':email', $email);
$stmt_check_email->execute();

if ($stmt_check_email->rowCount() > 0) {
    die("<h1>❌ Error de Registro</h1><p>Este correo electrónico ya está registrado.</p>");
}

// 4. HASHING SEGURO DE LA CONTRASEÑA
$password_hash_seguro = password_hash($password, PASSWORD_DEFAULT);

// 5. ASIGNACIÓN DEL ROL (Mapeo de Nombre a ID)
$sql_rol = "SELECT id FROM roles WHERE name = 'paciente'";
$stmt_rol = $pdo->prepare($sql_rol);
$stmt_rol->execute();
$role = $stmt_rol->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    die("<h1>❌ Error de Rol</h1><p>El rol paciente no exite en la base de datos.</p>");
}

$role_id = $role['id'];

// 6. INSERCIÓN FINAL DEL USUARIO EN LA BD

$sql_insert = "INSERT INTO users (first_name, last_name, email, password_hash, role_id, weight, height) 
               VALUES (:primerNombre, :apellido, :email, :password_hash, :role_id, :weight, :height)";

$stmt_insert = $pdo->prepare($sql_insert);

// Asignación de parámetros
$stmt_insert->bindParam(':primerNombre', $primerNombre);
$stmt_insert->bindParam(':apellido', $apellido);
$stmt_insert->bindParam(':email', $email);
$stmt_insert->bindParam(':password_hash', $password_hash_seguro);
$stmt_insert->bindParam(':role_id', $role_id, PDO::PARAM_INT);

$stmt_insert->bindParam(':weight', $peso);
$stmt_insert->bindParam(':height', $altura);

try {
    $stmt_insert->execute();

    // REDIRECCIÓN CON NOTIFICACIÓN DE ÉXITO (PRG)
    // El script redirige al Nutriólogo de vuelta a la lista de pacientes con el nombre en la URL.
    $mensaje_exito = "Paciente " . urlencode($primerNombre . " " . $apellido) . " registrado con exito.";
    
    header("Location: ../HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=success&msg=" . $mensaje_exito);

} catch (PDOException $e) {
    // Si hay error, redirigir con mensaje de error (opcional)
    $mensaje_error = "Error al registrar: " . urlencode($e->getMessage());
    header("Location: ../HTML/NUTRIOLOGO/nutriologo_mis_pacientes.html?status=error&msg=" . $mensaje_error);
    exit();
}
?>
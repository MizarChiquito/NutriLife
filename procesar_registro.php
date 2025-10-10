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
$rol_nombre   = trim(htmlspecialchars($_POST['rol'] ?? ''));

// 2.3 Validación de campos obligatorios
if (empty($primerNombre) || empty($apellido) || empty($email) || empty($password) || empty($rol_nombre)) {
    die("<h1>❌ Error de Validación</h1><p>Todos los campos son obligatorios.</p>");
}

// 2.4 Validación de Email (Formato)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("<h1>❌ Error de Validación</h1><p>El formato del correo electrónico es incorrecto.</p>");
}

// 2.5 Validación de Contraseña (Longitud y Requisitos Mínimos)
if (strlen($password) < 8 || strlen($password) > 12) {
    die("<h1>❌ Error de Validación</h1><p>La contraseña debe tener entre 8 y 12 caracteres.</p>");
}

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
$sql_rol = "SELECT id FROM roles WHERE name = :rol_nombre";
$stmt_rol = $pdo->prepare($sql_rol);
$stmt_rol->bindParam(':rol_nombre', $rol_nombre);
$stmt_rol->execute();
$role = $stmt_rol->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    die("<h1>❌ Error de Rol</h1><p>El rol de usuario seleccionado no es válido.</p>");
}

$role_id = $role['id'];

// 6. INSERCIÓN FINAL DEL USUARIO EN LA BD

$sql_insert = "INSERT INTO users (first_name, last_name, email, password_hash, role_id) 
               VALUES (:primerNombre, :apellido, :email, :password_hash, :role_id)";

$stmt_insert = $pdo->prepare($sql_insert);

// Asignación de parámetros
$stmt_insert->bindParam(':primerNombre', $primerNombre);
$stmt_insert->bindParam(':apellido', $apellido);
$stmt_insert->bindParam(':email', $email);
$stmt_insert->bindParam(':password_hash', $password_hash_seguro);
$stmt_insert->bindParam(':role_id', $role_id, PDO::PARAM_INT);

try {
    $stmt_insert->execute();

    // 1. Crear un mensaje de éxito para mostrar en el login
    $msg = urlencode("✅ ¡Registro Exitoso! Ahora puedes iniciar sesión.");

    // 2. Redirigir al usuario a login.html
    header("Location: login.html?status=success&msg=" . $msg);
    exit(); // Detiene el script después de la redirección

    echo "<p>El usuario <strong>$primerNombre $apellido</strong> ha sido registrado como <strong>$rol_nombre</strong>.</p>";

} catch (PDOException $e) {
    die("<h1>❌ Error al Registrar Usuario</h1><p>Ocurrió un error inesperado al guardar los datos: " . $e->getMessage() . "</p>");
}
?>

<?php
// paciente_actualizar_personal.php
global $pdo;
session_start();
require_once 'conexion.php';

// 1. Seguridad: Verificar autenticación y rol
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Paciente') {
    $msg = urlencode("Acceso no autorizado.");
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg);
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Recepción y Sanitización de Datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método de solicitud no válido.");
}

$primerNombre = trim(htmlspecialchars($_POST['primerNombre'] ?? ''));
$apellido     = trim(htmlspecialchars($_POST['apellido'] ?? ''));
$email        = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);


// 3. Validación de Datos
if (empty($primerNombre) || empty($apellido) || !$email) {
    $error_msg = urlencode("El nombre, apellido y un email válido son obligatorios.");
    // NOTA: Redirige al formulario de datos personales
    header("Location: paciente_perfil_personal.php?status=error&msg=" . $error_msg);
    exit();
}

try {
    // 4. Sentencia SQL para ACTUALIZAR los datos en la tabla 'users'
    $sql_update = 'UPDATE users SET 
                   first_name = :primerNombre, 
                   last_name = :apellido, 
                   email = :email 
                   WHERE id = :user_id';

    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':primerNombre', $primerNombre);
    $stmt_update->bindParam(':apellido', $apellido);
    $stmt_update->bindParam(':email', $email);
    $stmt_update->bindParam(':user_id', $user_id);

    $stmt_update->execute();

    // 5. Actualizar variable de sesión si el nombre cambió
    $_SESSION['user_name'] = $primerNombre;

    // 6. Redirección de Éxito
    $success_msg = urlencode("✅ Datos personales actualizados correctamente.");
    // NOTA: Redirige al formulario de datos personales
    header("Location: paciente_perfil_personal.php?status=success&msg=" . $success_msg);
    exit();

} catch (PDOException $e) {
    // Manejo de error de la BD (ej. email duplicado)
    $error_msg = urlencode("❌ Error al actualizar datos personales: " . $e->getMessage());
    header("Location: paciente_perfil_personal.php?status=error&msg=" . $error_msg);
    exit();
}
?>
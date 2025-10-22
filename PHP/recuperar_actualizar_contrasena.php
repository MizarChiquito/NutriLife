<?php
// recuperar_actualizar_contrasena.php
date_default_timezone_set('America/Mexico_City');

// Asegúrate de incluir tu archivo de conexión a la base de datos.
global $pdo;
require_once 'conexion.php';

// Tiempo máximo de validez del token en segundos (ej: 1 hora)
const TOKEN_EXPIRY_SECONDS = 3600;

// 1. Recepción y Sanitización de Datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método de solicitud no válido.");
}

$token = trim($_POST['token'] ?? '');
$nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
$confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

// 2. Validación de la Contraseña
if (empty($token) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
    $msg = urlencode("Token y contraseñas son campos obligatorios.");
    // Redirige al formulario de reestablecimiento (asumiendo que se llama 'recuperar_contrasena_form.php')
    header("Location: recuperar_contrasena_form.php?error=" . $msg);
    exit();
}

if ($nueva_contrasena !== $confirmar_contrasena) {
    $msg = urlencode("Las contraseñas no coinciden.");
    header("Location: recuperar_contrasena_form.php?token=" . urlencode($token) . "&error=" . $msg);
    exit();
}

// 3. Verificar el Token de Recuperación
try {
    $sql_token = 'SELECT user_id, created_at FROM password_resets WHERE token = :token';
    $stmt_token = $pdo->prepare($sql_token);
    $stmt_token->bindParam(':token', $token);
    $stmt_token->execute();
    $reset_data = $stmt_token->fetch(PDO::FETCH_ASSOC);

    if (!$reset_data) {
        $msg = urlencode("Enlace de recuperación inválido o expirado. Solicite uno nuevo.");
        header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg);
        exit();
    }

    // Verificar si el token ha expirado
    $created_time = strtotime($reset_data['created_at']);
    if (time() - $created_time > TOKEN_EXPIRY_SECONDS) {
        $msg = urlencode("El enlace de recuperación ha expirado. Por favor, solicite uno nuevo.");

        // Limpiar el token expirado (Buena práctica)
        $sql_delete = 'DELETE FROM password_resets WHERE token = :token';
        $pdo->prepare($sql_delete)->execute(['token' => $token]);

        header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg);
        exit();
    }

    $user_id = $reset_data['user_id'];

    // 4. Cifrado y Actualización de la Contraseña en la tabla 'users'
    $hashed_password = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

    $sql_update = 'UPDATE users SET password_hash = :password WHERE id = :user_id';
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':password', $hashed_password);
    $stmt_update->bindParam(':user_id', $user_id);
    $stmt_update->execute();

    // 5. Eliminar el Token de Recuperación después de su uso (CLAVE)
    $sql_delete = 'DELETE FROM password_resets WHERE user_id = :user_id';
    $pdo->prepare($sql_delete)->execute(['user_id' => $user_id]);

    // 6. Redirección de Éxito
    $msg = urlencode("✅ Contraseña actualizada con éxito. Ahora puede iniciar sesión.");
    header("Location: ../HTML/GENERALES/login.html?status=success&msg=" . $msg);
    exit();

} catch (PDOException $e) {
    // Manejo de error de la BD
    $error_msg = urlencode("❌ Error de Base de Datos: " . $e->getMessage());
    header("Location: recuperar_contrasena_form.php?token=" . urlencode($token) . "&error=" . $error_msg);
    exit();
}
?>
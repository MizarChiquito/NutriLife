<?php
// procesar_login.php
// Maneja el inicio de sesión de Nutrilife con validación de roles y contraseñas seguras.

// 1. Iniciar sesión y cargar conexión
session_start();
require_once 'conexion.php';
global $pdo;

// 2. Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $msg = urlencode("Acceso no autorizado.");
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}

// 3. Sanitizar entradas
$email = trim(htmlspecialchars($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

// 4. Validar campos vacíos
if (empty($email) || empty($password)) {
    $msg = urlencode("Debe ingresar su correo electrónico y su contraseña.");
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}

try {
    // 5. Buscar usuario y su rol
    $sql = "SELECT u.id, u.first_name, u.password_hash, r.name AS role_name 
            FROM users u 
            INNER JOIN roles r ON u.role_id = r.id 
            WHERE u.email = :email
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 6. Verificar existencia y contraseña
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $msg = urlencode("Credenciales incorrectas.");
        header("Location: login.html?status=error&msg=" . $msg);
        exit();
    }

    // 7. Configurar sesión (inicio exitoso)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'];
    $_SESSION['role'] = $user['role_name'];
    $_SESSION['LAST_ACTIVITY'] = time();

    // 8. Redirigir según rol
    switch ($user['role_name']) {
        case 'Administrador':
            header("Location: admin_dashboard.php");
            break;
        case 'Nutriologo':
            header("Location: nutriologo_dashboard.php");
            break;
        case 'Paciente':
            header("Location: paciente_dashboard.php");
            break;
        default:
            session_unset();
            session_destroy();
            $msg = urlencode("Rol no válido o no asignado.");
            header("Location: login.html?status=error&msg=" . $msg);
            break;
    }
    exit();

} catch (PDOException $e) {
    $msg = urlencode("Error de conexión: " . $e->getMessage());
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}
?>

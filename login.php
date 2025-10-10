<?php
session_start();
require_once 'conexion.php';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html?status=error&msg=" . urlencode("Método no permitido."));
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    header("Location: login.html?status=error&msg=" . urlencode("Completa todos los campos."));
    exit();
}

try {
    global $pdo;

    // Buscar usuario por correo
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Crear sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['role'] = match ($user['role_id']) {
            1 => 'Nutriologo',
            2 => 'Paciente',
            3 => 'Administrador',
            default => 'Desconocido'
        };

        // Redirección según rol
        switch ($user['role_id']) {
            case 3:
                header("Location: admin_dashboard.php");
                break;
            case 1:
                header("Location: nutriologo_dashboard.php");
                break;
            case 2:
                header("Location: paciente_dashboard.php");
                break;
            default:
                header("Location: login.html?status=error&msg=" . urlencode("Rol no válido."));
                break;
        }
        exit();
    } else {
        header("Location: login.html?status=error&msg=" . urlencode("Credenciales incorrectas."));
        exit();
    }
} catch (PDOException $e) {
    header("Location: login.html?status=error&msg=" . urlencode("Error de conexión a BD."));
    exit();
}
?>

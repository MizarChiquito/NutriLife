<?php
// ver_usuarios.php
// Este archivo muestra la lista de todos los usuarios registrados (solo accesible al Administrador).

require_once 'check_session.php';
require_once 'conexion.php';
global $pdo;

// 1. Verificar que el usuario logueado sea Administrador
if ($_SESSION['role'] !== 'Administrador') {
    header("Location: login.html?status=error&msg=" . urlencode("Acceso denegado. Solo los administradores pueden acceder a esta vista."));
    exit();
}

// 2. Consultar usuarios y sus roles
$sql = "SELECT u.id, u.first_name, u.last_name, u.email, r.name AS role_name
        FROM users u
        JOIN roles r ON u.role_id = r.id
        ORDER BY u.id ASC";

$stmt = $pdo->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Usuarios Registrados | Administrador</title>
    <link rel="stylesheet" href="administrador.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        h1 {
            color: #2c3e50;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #27ae60;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        a {
            color: #2980b9;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .back-btn {
            margin-top: 20px;
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <h1>👥 Usuarios Registrados</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Rol</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($usuarios) > 0): ?>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['first_name']) ?></td>
                        <td><?= htmlspecialchars($u['last_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No hay usuarios registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="back-btn">⬅ Volver al Panel de Administración</a>
</body>
</html>

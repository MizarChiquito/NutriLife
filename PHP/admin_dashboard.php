<?php
// admin_dashboard.php
// Este script es el guardián del Administrador.
// NOTA: 'check_session.php' manejará la excepción de la expiración para este rol.

require_once 'check_session.php';

// 1. Verificar el Rol: Debe ser 'Administrador'
// Aunque check_session.php ya lo verifica, este es un doble chequeo de seguridad.
if ($_SESSION['role'] !== 'Administrador') {
    // Redirige si el usuario no es un Administrador
    header("Location: login.html?status=error&msg=" . urlencode("Acceso denegado. Este área es solo para Administradores."));
    exit();
}

// 2. Definir datos de sesión para inyección
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario');
// Estilo para el botón de Cerrar Sesión (color rojo/oscuro para el Admin)
$logout_link = '<a href="logout.php" style="color: #c0392b; text-decoration: none; font-weight: bold;">Cerrar Sesión</a>';


// 3. Cargar el contenido HTML
$html_content = file_get_contents('administrador.html');

// 4. Reemplazar Marcadores en el HTML

// a) Inyectar el saludo y título en el H2
// <h2 id="admin-title">Panel de Administrador | Cargando...</h2>
$new_title_h2 = "<h2>Panel de Administrador | Administrador {$user_name}</h2>";
$html_content = str_replace('<h2 id="admin-title">Panel de Administrador</h2>', $new_title_h2, $html_content);

// b) Inyectar el enlace de cerrar sesión en el DIV
// Busca: <div id="logout-button-area" style="text-align: right; margin-bottom: 20px;">
$html_content = str_replace('<nav id="logout-button-area" style="text-align: right; margin-bottom: 20px;"></nav>',
    '<nav id="logout-button-area" style="text-align: right; margin-bottom: 20px;">' . $logout_link . '</nav>',
    $html_content);

// 5. Mostrar el HTML modificado
echo $html_content;
?>
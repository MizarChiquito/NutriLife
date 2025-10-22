<?php
// paciente_dashboard.php
// Este script verifica la sesión, el cierre automático y la seguridad.

require_once 'check_session.php';

// 1. Verificar el Rol: Debe ser 'Paciente'
if ($_SESSION['role'] !== 'Nutriologo') {
    // Si el usuario no es nutriologo, redirige y finaliza.
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . urlencode("Acceso denegado. Este área es solo para nutriologos."));
    exit();
}

// 2. Definir datos de sesión para inyección (opcional, pero útil)
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario');
$logout_link = '<a href="logout.php" style="color: #007bff; text-decoration: none; font-weight: bold;">Cerrar Sesión</a>';


// 3. Cargar y procesar el HTML (Usando el archivo correcto)
$html_content = file_get_contents('../HTML/NUTRIOLOGO/nutriologo.html');

// Inyectar el saludo al usuario en el H2 (usando el método de reemplazo de cadenas que establecimos)
$new_title_h2 = "<h2>¡Bienvenido, Nutriologo {$user_name}!</h2>";
$html_content = str_replace('<h2 id="nutriolgo-title">¡Bienvenido!</h2>', $new_title_h2, $html_content);

// Inyectar el enlace de cerrar sesión en el NAV
$html_content = str_replace('<nav id="logout-area" style="text-align: right; margin-top: 20px;"></nav>',
    '<nav id="logout-area" style="text-align: right; margin-top: 20px;">' . $logout_link . '</nav>',
    $html_content);

// 4. Mostrar el HTML modificado
echo $html_content;

?>
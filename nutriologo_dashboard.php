<?php
// nutriologo_dashboard.php
require_once 'check_session.php'; // 1. Verifica la sesión y timeout

// 2. Verifica si el rol tiene permiso para esta página
if ($_SESSION['role'] !== 'Nutriologo') {
    header("Location: login.html?status=error&msg=" . urlencode("Acceso denegado."));
    exit();
}

// 3. Incluir el archivo de contenido visual (que SÍ tiene la extensión .html)
include 'nutriologo.html';
?>
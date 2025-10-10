<?php
// admin_dashboard.php
require_once 'check_session.php';

// Verifica el rol de Administrador
if ($_SESSION['role'] !== 'Administrador') {
    header("Location: login.html?status=error&msg=" . urlencode("Acceso solo para Administradores."));
    exit();
}

// Incluir el archivo de contenido visual
include 'administrador.html';
?>
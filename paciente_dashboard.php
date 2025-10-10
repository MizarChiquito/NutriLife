<?php
// paciente_dashboard.php
require_once 'check_session.php';

// Verifica el rol de Administrador
if ($_SESSION['role'] !== 'Paciente') {
    header("Location: login.html?status=error&msg=" . urlencode("Acceso solo para Pacientes."));
    exit();
}

// Incluir el archivo de contenido visual
include 'Paciente.html';
?>
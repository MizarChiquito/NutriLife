<?php
// check_session.php

session_start();

// Tiempo de expiración: 1 hora de inactividad
$expire_time = 3600;

// 1. Redirección si NO hay sesión activa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $msg = urlencode("Acceso denegado. Por favor, inicie sesión.");
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg);
    exit();
}

// 2. EXCEPCIÓN: Si el rol es Administrador, saltamos la verificación de inactividad.
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrador') {
    // Si es Administrador, solo actualizamos la actividad para evitar problemas de sesión de PHP
    $_SESSION['LAST_ACTIVITY'] = time();
    // Salimos del script, SIN verificar la inactividad.
    return; // Usamos 'return' para salir sin ejecutar el resto del código de inactividad.
}

// 3. Cierre de Sesión Automático (Inactividad)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $expire_time)) {
    session_unset();
    session_destroy();
    $msg = urlencode("Su sesión ha expirado por inactividad.");
    header("Location: ../HTML/GENERALES/login.html?status=timeout&msg=" . $msg);
    exit();
}

// 4. Actualizar la actividad (para mantener la sesión viva)
$_SESSION['LAST_ACTIVITY'] = time();
?>
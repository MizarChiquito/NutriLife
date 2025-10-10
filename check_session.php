<?php
// check_session.php

session_start();

// Tiempo de expiración: 1 hora de inactividad
$expire_time = 3600;

// 1. Redirección si NO hay sesión activa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $msg = urlencode("Acceso denegado. Por favor, inicie sesión.");
    header("Location: login.html?status=error&msg=" . $msg);
    exit();
}

// 2. Cierre de Sesión Automático (Inactividad)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $expire_time)) {
    session_unset();
    session_destroy();
    $msg = urlencode("Su sesión ha expirado por inactividad.");
    header("Location: login.html?status=timeout&msg=" . $msg);
    exit();
}

// 3. Actualizar la actividad (para mantener la sesión viva)
$_SESSION['LAST_ACTIVITY'] = time();
?>
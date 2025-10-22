<?php
// logout.php
session_start();

session_unset();
session_destroy();

$msg = urlencode("Sesión cerrada correctamente.");
header("Location: ../HTML/GENERALES/login.html?status=logout&msg=" . $msg);
exit();
?>
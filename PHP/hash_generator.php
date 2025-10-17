<?php
// hash_generator.php
$nueva_contraseña_plana = "AdminSeguro!2025"; // ¡USA UNA CONTRASEÑA NUEVA Y SEGURA!
$hash_generado = password_hash($nueva_contraseña_plana, PASSWORD_DEFAULT);
echo "Nuevo Hash: " . $hash_generado . "\n";
// Ejemplo de salida: $2y$10$RzEw3Oa2kH9YyM4pC7bFfOQfG3aT6U2C... (¡Copia esto!)
?>
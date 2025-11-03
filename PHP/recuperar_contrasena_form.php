<?php
// recuperar_contrasena_form.php (Formulario que recibe el token por GET)

// No requiere require_once 'conexion.php' a menos que quieras validar el token aquí.
// Pero por simplicidad, solo lo validamos en el script de actualización.

$error_message = '';
$token = htmlspecialchars($_GET['token'] ?? '');

if (isset($_GET['error'])) {
    $error_message = '<p style="color: red; font-weight: bold;">' . htmlspecialchars(urldecode($_GET['error'])) . '</p>';
}

// Validación básica si no hay token
if (empty($token)) {
    $msg = urlencode("Falta el token de recuperación. Por favor, use el enlace completo de su correo.");
    // Redirige al login para solicitar el proceso de nuevo
    header("../HTML/GENERALES/login.html" . $msg);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="/Nutrilife/CSS/recuperar_contraseña.css">
</head>
<body>
<h1><strong>NUTRILIFE</strong></h1>
<div id="form-container">
    <p class ="a">Recuperar contraseña</p>

    <?php echo $error_message; // Muestra errores de validación ?>

    <form method="POST" action="recuperar_actualizar_contrasena.php">

        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <p><label>Nueva Contraseña:</label>
            <input type="password" name="nueva_contrasena" required minlength="8"
                   placeholder="Mínimo 8 caracteres"></p>

        <p><label>Confirmar Contraseña:</label>
            <input type="password" name="confirmar_contrasena" required minlength="8"
                   placeholder="Confirme la nueva contraseña"></p>

        <p style="text-align: center; margin-top: 20px;">
            <input type="submit" value="Guardar" />
    </form>
</div>
</body>
</html>
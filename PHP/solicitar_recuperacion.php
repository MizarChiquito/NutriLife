<?php
// solicitar_recuperacion.php (Generación de token y envío de email)
date_default_timezone_set('America/Mexico_City');

global $pdo;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'conexion.php'; // Asumimos que $pdo se define aquí

// 1. Recepción y Sanitización de Datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método de solicitud no válido.");
}
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (empty($email)) {
    $msg = urlencode("Por favor, ingrese su dirección de correo electrónico.");
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $msg);
    exit();
}

try {
    // 2. Verificar si el usuario existe
    $sql_user = 'SELECT id, first_name FROM users WHERE email = :email';
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->bindParam(':email', $email);
    $stmt_user->execute();
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id'];
        $token = bin2hex(random_bytes(32)); // Generación de token seguro

        // 3. Insertar Token en la tabla 'password_resets' (Transacción para Atomicidad)
        $pdo->beginTransaction();

        // Limpiar tokens anteriores para este usuario
        $sql_delete = 'DELETE FROM password_resets WHERE user_id = :user_id';
        $pdo->prepare($sql_delete)->execute(['user_id' => $user_id]);

        // Insertar el nuevo token
        $sql_insert = 'INSERT INTO password_resets (user_id, token) VALUES (:user_id, :token)';
        $stmt_insert = $pdo->prepare($sql_insert);
        $stmt_insert->bindParam(':user_id', $user_id);
        $stmt_insert->bindParam(':token', $token);
        $stmt_insert->execute();

        $pdo->commit();

        // 4. Envío de Email con Enlace de Recuperación
        require '../vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {
            // Configuración del Servidor (Usando SMTP de Gmail)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';  // Servidor SMTP de Google
            $mail->SMTPAuth   = true;
            $mail->Username   = 'acostacomparanh@gmail.com'; // correo
            $mail->Password   = 'obea wfmz oyfl tuvn';     // CONTRASEÑA DE APLICACIÓN DE 16 DÍGITOS
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL/TLS
            $mail->Port       = 465; // Puerto para SMTPS

            // Configuración del Correo
            $mail->setFrom('SU_CORREO@gmail.com', 'Equipo NUTRILIFE'); // Remitente
            $mail->addAddress($email, $user['first_name']);             // Destinatario
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $base_url = "http://localhost/Nutrilife/Nutrilife/PHP/";

            $reset_link = $base_url . "recuperar_contrasena_form.php?token=" . urlencode($token);

            $reset_link2 = "recuperar_contrasena_form.php?token=" . urlencode($token);

            $mail->Subject = 'Recuperación de Contraseña - NUTRILIFE';

            // Definir el cuerpo del mensaje en formato HTML
            $mail->Body    = "
                <html>
                <head>
                  <title>Recuperación de Contraseña</title>
                </head>
                <body>
                  <h2>Hola " . htmlspecialchars($user['first_name']) . ",</h2>
                  <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
                  <p>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                  <p style='margin: 20px 0; padding: 10px; background-color: #f0f0f0; border: 1px solid #ccc; text-align: center;'>
                    <a href='{$reset_link}' style='color: #1b5e20; text-decoration: none; font-weight: bold;'>
                        Restablecer mi Contraseña
                    </a>
                  </p>
                  <p>Este enlace expirará en una hora. Si no solicitaste este cambio, por favor, ignora este correo.</p>
                  <p>Atentamente,<br>Equipo NUTRILIFE</p>
                </body>
                </html>
            ";

            $mail->send();

        } catch (Exception $e) {
            // Manejo del error de envío
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            // Por seguridad, NO muestre el error al usuario, mantenga el mensaje genérico.
        }

        echo "<h1>✅ Token Generado con Éxito (Modo Debug)</h1>";
        echo "<p>El sistema generó el siguiente enlace. Úsalo para continuar el proceso:</p>";
        echo "<p>Enlace servidor apache Xampp:</p>";
        echo '<p style="font-size: 1.1em; font-weight: bold;"><a href="' . $reset_link . '">' . htmlspecialchars($reset_link) . '</a></p>';
        echo "<p>Enlace del archivo php:</p>";
        echo '<p style="font-size: 1.1em; font-weight: bold;"><a href="' . $reset_link2 . '">' . htmlspecialchars($reset_link) . '</a></p>';
    }

    // 5. Redirección de Éxito (Mensaje genérico por seguridad)
    $msg = urlencode("Si el correo electrónico existe, se ha enviado un enlace de recuperación. Revise su bandeja de entrada.");

    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $error_msg = urlencode("❌ Error en la base de datos al generar el token.");
    header("Location: ../HTML/GENERALES/login.html?status=error&msg=" . $error_msg);
    exit();
}
?>
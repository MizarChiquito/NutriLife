<?php
// Vista del perfil del Nutriólogo
global $status, $msg, $user_data;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Nutriólogo</title>
    <link rel="stylesheet" href="../CSS/datos_personales_paciente.css">
    <style>
        .message.success { color: #155724; background-color: #d4edda; }
        .message.error { color: #721c24; background-color: #f8d7da; }

        /* Estilo para el enlace de logout */
        #logout-area {
            text-align: right;
            margin-top: 20px;
        }
        #logout-area a {
            color: #c0392b; /* Rojo oscuro */
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
        }
    </style>
    <script>
        // Funciones JS para habilitar/cancelar edición
        function habilitarEdicion() {
            document.getElementById('first_name').disabled = false;
            document.getElementById('last_name').disabled = false;
            document.getElementById('email').disabled = false;

            document.getElementById('btn-modificar').style.display = 'none';
            document.getElementById('btn-guardar').style.display = 'inline-block';
            document.getElementById('btn-cancelar').style.display = 'inline-block';
        }

        function cancelarEdicion() {
            window.location.reload();
        }

        function validarDatosPersonales(event) {
            const first_name = document.getElementById('first_name').value.trim();
            const last_name = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();

            if (first_name === '' || last_name === '' || email === '') {
                alert('Todos los campos de nombre y email son obligatorios.');
                event.preventDefault(); return false;
            }
            if (!email.includes('@') || !email.includes('.')) {
                alert('Por favor, ingrese un formato de correo electrónico válido.');
                event.preventDefault(); return false;
            }
            const confirmacion = confirm('¿Está seguro de que desea guardar estos cambios?');
            if (!confirmacion) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</head>

<body>
<div class="container">
    <h1>Mi Perfil - Datos Personales</h1>
    <p class="subtitle" style="font-size: 0.9em; margin-top: -10px;">Actualiza tu nombre y correo de contacto.</p>

    <?php if (!empty($msg)): ?>
        <p class="message <?php echo $status; ?>"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="post" action="nutriologo_actualizar_personal.php" onsubmit="return validarDatosPersonales(event)" class="data-form">

        <div class="form-group">
            <label for="first_name">Nombre:</label>
            <input type="text" id="first_name" name="first_name"
                   value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>" placeholder="Tu nombre" disabled required />
        </div>

        <div class="form-group">
            <label for="last_name">Apellido:</label>
            <input type="text" id="last_name" name="last_name"
                   value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>" placeholder="Tu apellido" disabled required />
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
                   value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" placeholder="tu@correo.com" disabled required />
        </div>

        <hr style="margin: 20px 0;">

        <p style="text-align: center; margin-top: 20px;">
            <input type="button" id="btn-modificar" value="Modificar Datos" onclick="habilitarEdicion()" class="btn btn-primary" />
            <input type="submit" id="btn-guardar" value="Guardar Cambios" class="btn btn-primary" style="display: none;" />
            <input type="button" id="btn-cancelar" value="Cancelar" onclick="cancelarEdicion()" class="btn btn-secondary" style="display: none;" />
        </p>
    </form>

    <nav id="logout-area">
        <a href="/NutriLife/PHP/logout.php">Cerrar Sesión</a>
    </nav>

</div>
</body>
</html>
global$status; global$status; <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Datos Personales</title>
    <link rel="stylesheet" href="../CSS/datos_personales_paciente.css">
    <style>
        /* Estilos específicos para este formulario */
        .message.success { color: #155724; background-color: #d4edda; }
        .message.error { color: #721c24; background-color: #f8d7da; }
    </style>
    <script>
        // Función para habilitar los campos (mantiene la lógica)
        function habilitarEdicion() {
            document.getElementById('first_name').disabled = false;
            document.getElementById('last_name').disabled = false;
            document.getElementById('email').disabled = false;

            document.getElementById('btn-modificar').style.display = 'none';
            document.getElementById('btn-guardar').style.display = 'inline-block';
            document.getElementById('btn-cancelar').style.display = 'inline-block';
        }

        // Función para deshabilitar los campos y recargar (cancelar)
        function cancelarEdicion() {
            window.location.reload();
        }

        // Validación y Confirmación Final del Formulario
        function validarDatosPersonales(event) {
            // 1. Validaciones existentes
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

            // 2. VENTANA DE CONFIRMACIÓN DE CAMBIOS (CÓDIGO NUEVO)
            const confirmacion = confirm('¿Está seguro de que desea guardar estos cambios en sus datos personales?');

            if (!confirmacion) {
                // Si el usuario presiona "Cancelar"
                event.preventDefault();
                return false;
            }

            // Si el usuario presiona "Aceptar" y la validación pasa, el formulario se envía
            return true;
        }
    </script>
</head>

<body>
<div class="container">
    <h1>Mi Perfil - Datos Personales</h1>
    <p class="subtitle" style="font-size: 0.9em; margin-top: -10px;">Visualiza y actualiza tu nombre y correo.</p>

    <?php if (!empty($msg)): ?>
        <p class="message <?php echo $status; ?>"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="post" action="actualizar_datos.php" onsubmit="return validarDatosPersonales(event)" class="data-form">

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

            <hr style="margin: 25px 0; border-top: 1px solid #ddd;">

            <p style="text-align: center;">
                ¿Desea modificar sus datos de salud (Peso, Altura, Alergias)?
            </p>

            <a href="paciente_perfil_salud_vista.php" class="btn btn-secondary-link">
                Ir a Modificar Datos de Salud
            </a>
        </div>

        <hr style="margin: 20px 0;">

        <p style="text-align: center; margin-top: 20px;">
            <input type="button" id="btn-modificar" value="Modificar Datos" onclick="habilitarEdicion()" class="btn btn-primary" />

            <input type="submit" id="btn-guardar" value="Guardar Cambios" class="btn btn-primary" style="display: none;" />

            <input type="button" id="btn-cancelar" value="Cancelar" onclick="cancelarEdicion()" class="btn btn-secondary" style="display: none;" />
        </p>
    </form>
</div>
</body>
</html>
<?php
// paciente_perfil_salud_vista.php (VISTA - Ajustada para guardar texto libre en la columna principal)

global $msg, $status, $has_data, $current_action;

// Valores de datos del formulario (para prellenar el formulario)
$peso_actual = $health_data['peso'] ?? '';
$altura_actual = $health_data['altura'] ?? '';
$db_intolerancias = $health_data['intolerancias'] ?? ''; // Valor crudo de la BD

// 1. Definir las opciones del SELECT
$opciones_validas = ['ninguna', 'gluten', 'lactosa', 'frutos_secos'];

// 2. Determinar el valor del SELECT y del campo de texto
$intolerancias_actual = 'seleccionar'; // Por defecto
$especificar_actual = '';              // Por defecto

if (in_array($db_intolerancias, $opciones_validas)) {
    // Caso 1: El valor de la BD es una opción predefinida.
    $intolerancias_actual = $db_intolerancias;
} elseif (!empty($db_intolerancias)) {
    // Caso 2: El valor de la BD es texto libre (asumimos que es "Otro").
    $intolerancias_actual = 'otro';
    $especificar_actual = $db_intolerancias;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Mi Perfil - Datos de Salud</title>
    <link rel="stylesheet" href="../CSS/paciente_perfil_salud.css">
    <style>
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .btn-secondary-link {
            display: block; width: 90%; margin: 15px auto 0; padding: 10px;
            border: 1px solid #388e3c; border-radius: 4px; font-size: 1em;
            background-color: #ffffff; color: #388e3c; text-decoration: none;
            text-align: center; transition: background-color 0.3s ease;
        }
        .btn-secondary-link:hover { background-color: #e8f5e9; }
    </style>
    <script>
        // Función para habilitar los campos
        function habilitarEdicion() {
            document.getElementById('peso').disabled = false;
            document.getElementById('altura').disabled = false;
            document.getElementById('int/ale').disabled = false;
            document.getElementById('int/ale-especificar').disabled = false;

            // Ocultar botones de acción y mostrar Guardar/Cancelar
            document.getElementById('btn-modificar').style.display = 'none';
            document.getElementById('btn-eliminar').style.display = 'none';
            document.getElementById('btn-guardar').style.display = 'inline-block';
            document.getElementById('btn-cancelar').style.display = 'inline-block';
        }

        function cancelarEdicion() {
            window.location.reload();
        }

        // Validación y Confirmación Final del Formulario
        function validarPerfil(event) {
            const peso = document.getElementById('peso').value;
            const altura = document.getElementById('altura').value;
            const intolerancia_select = document.getElementById('int/ale').value;
            const intolerancia_especificar = document.getElementById('int/ale-especificar').value.trim();

            if (peso === '' || altura === '' || isNaN(peso) || isNaN(altura) || parseFloat(peso) <= 0 || parseFloat(altura) <= 0) {
                alert('Por favor, ingrese valores válidos y positivos para Peso y Altura.');
                event.preventDefault(); return false;
            }
            if (intolerancia_select === 'otro' && intolerancia_especificar === '') {
                alert('Ha seleccionado "Otro / Múltiple". Por favor, especifique sus intolerancias.');
                event.preventDefault(); return false;
            }

            const actionText = 'guardar';
            const confirmacion = confirm(`¿Está seguro de que desea ${actionText} estos datos de salud?`);

            if (!confirmacion) {
                event.preventDefault();
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
<h1><strong>NUTRILIFE</strong></h1>

<div id="form-container">
    <p class="a">Mi Perfil - Datos extra</p>
    <p class="a" style="font-size: 0.9em; margin-top: -10px;">Visualiza y actualiza tu peso y altura.</p>

    <?php if (!empty($msg)): ?>
        <p class="message <?php echo $status; ?>"><?php echo $msg; ?></p>
    <?php endif; ?>

    <form method="post" action="paciente_perfil_salud_CRUD.php" onsubmit="return validarPerfil(event)">

        <input type="hidden" id="action" name="action" value="<?php echo $current_action; ?>" />

        <h2>Datos extras</h2>

        <p><label>Peso (kg):
                <input type="number" id="peso" name="peso" step="0.01" min="30" max="300"
                       value="<?php echo htmlspecialchars($peso_actual); ?>"
                       placeholder="Peso en kilogramos" disabled required />
            </label></p>

        <p><label>Altura (m):
                <input type="number" id="altura" name="altura" step="0.01" min="0.50" max="3.00"
                       value="<?php echo htmlspecialchars($altura_actual); ?>"
                       placeholder="Altura en metros" disabled required />
            </label></p>

        <hr style="margin: 20px 0;">

        <p><label>Intolerancias/Alergias:
                <select id="int/ale" name="int/ale" disabled>
                    <option value="seleccionar" disabled <?php if ($intolerancias_actual === 'seleccionar') echo 'selected'; ?>>-- Seleccione una opción --</option>
                    <option value="ninguna" <?php if ($intolerancias_actual === 'ninguna') echo 'selected'; ?>>Ninguna</option>
                    <option value="gluten" <?php if ($intolerancias_actual === 'gluten') echo 'selected'; ?>>Intolerancia al Gluten</option>
                    <option value="lactosa" <?php if ($intolerancias_actual === 'lactosa') echo 'selected'; ?>>Intolerancia a la Lactosa</option>
                    <option value="frutos_secos" <?php if ($intolerancias_actual === 'frutos_secos') echo 'selected'; ?>>Alergia a Frutos Secos</option>
                    <option value="otro" <?php if ($intolerancias_actual === 'otro') echo 'selected'; ?>>Otro / Múltiple</option>
                </select>
            </label></p>

        <p>
            <label>Especificar (solo si elige 'Otro'):
                <textarea id="int/ale-especificar" name="int/ale-especificar"
                          rows="4" placeholder="Escriba aquí si seleccionó Otro." disabled><?php echo htmlspecialchars($especificar_actual); ?></textarea>
            </label>
        </p>

        <hr style="margin: 20px 0;">

        <p style="text-align: center; margin-top: 20px;">
            <input type="button" id="btn-modificar"
                   value="<?php echo $has_data ? 'Modificar Datos' : 'Ingresar Datos'; ?>"
                   onclick="habilitarEdicion()"
                   style="display: inline-block;" />

            <input type="button" id="btn-eliminar" value="Eliminar Datos"
                   onclick="if(confirm('¿Está seguro de que desea eliminar todos sus datos de salud? Esta acción es irreversible y limpiará los campos.')) window.location.href='paciente_perfil_salud_CRUD.php?action=delete';"
                   style="display: <?php echo $has_data ? 'inline-block' : 'none'; ?>;" />

            <input type="submit" id="btn-guardar" value="Guardar Cambios" style="display: none;" />

            <input type="button" id="btn-cancelar" value="Cancelar" onclick="cancelarEdicion()" style="display: none; background-color: #dc3545;" />
        </p>
    </form>

    <hr style="margin: 25px 0; border-top: 1px solid #ddd;">

    <p style="text-align: center;">
        ¿Desea modificar sus datos personales (Nombre, Email)?
    </p>

    <a href="modificar_datos_paciente.php" class="btn-secondary-link">
        Ir a Modificar Datos Personales
    </a>

</div>
</body>
</html>
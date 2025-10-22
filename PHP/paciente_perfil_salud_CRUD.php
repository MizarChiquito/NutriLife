<?php
// paciente_perfil_salud_CRUD.php (Controlador Unificado - SIN columna 'especificar')

session_start();
require_once 'conexion.php'; // Asegúrate de que esta ruta define $pdo

// 1. Verificar Autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$health_data = null;
$msg = '';
$status = '';

// Obtener mensajes de estado
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars(urldecode($_GET['msg']));
    $status = $_GET['status'] ?? 'info';
}

// 2. MANEJO DE ACCIÓN POST (UPDATE / CREACIÓN inicial)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recepción y Sanitización de Datos
    $peso = filter_var($_POST['peso'] ?? '', FILTER_VALIDATE_FLOAT);
    $altura = filter_var($_POST['altura'] ?? '', FILTER_VALIDATE_FLOAT);
    $intolerancias_select = filter_var(trim($_POST['int/ale'] ?? ''), FILTER_SANITIZE_STRING); // Valor del select
    $especificar_texto = filter_var(trim($_POST['int/ale-especificar'] ?? ''), FILTER_SANITIZE_STRING); // Texto libre

    // 1. Validación de Peso/Altura
    if ($peso === false || $altura === false || $peso <= 0 || $altura <= 0) {
        $msg = urlencode("Valores de Peso/Altura inválidos. Deben ser números positivos.");
        header("Location: paciente_perfil_salud_CRUD.php?status=error&msg=" . $msg);
        exit();
    }

    // 2. Lógica de guardado en la ÚNICA columna 'intolerances_allergies'
    $intolerancias_final = NULL; // Variable que se guardará en la BD

    if ($intolerancias_select === 'otro') {
        if (empty($especificar_texto)) {
            $msg = urlencode("Debe especificar las intolerancias si selecciona 'Otro / Múltiple'.");
            header("Location: paciente_perfil_salud_CRUD.php?status=error&msg=" . $msg);
            exit();
        }
        // Si es 'otro' y tiene texto, guardamos el texto de la especificación
        $intolerancias_final = $especificar_texto;
    } elseif ($intolerancias_select === 'seleccionar') {
        // No guardamos nada (NULL)
        $intolerancias_final = NULL;
    } else {
        // Si es 'ninguna', 'gluten', 'lactosa', etc., guardamos el valor del select
        $intolerancias_final = $intolerancias_select;
    }

    try {
        // ACCIÓN: UPDATE (Modificar o Ingresar por primera vez)
        // ¡CORREGIDO! Solo usamos 'weight', 'height', y 'intolerances_allergies'
        $sql = 'UPDATE users SET weight = :peso, height = :altura, intolerances_allergies = :intolerancias_final 
                WHERE id = :user_id';

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':peso', $peso);
        $stmt->bindParam(':altura', $altura);
        $stmt->bindParam(':intolerancias_final', $intolerancias_final); // Valor final del select o texto libre

        $stmt->execute();

        $msg_success = "Datos de salud guardados con éxito.";
        header("Location: paciente_perfil_salud_CRUD.php?status=success&msg=" . urlencode($msg_success));
        exit();

    } catch (PDOException $e) {
        $msg = urlencode("Error al procesar los datos: " . $e->getMessage());
        header("Location: paciente_perfil_salud_CRUD.php?status=error&msg=" . $msg);
        exit();
    }
}

// 3. MANEJO DE ACCIÓN GET (DELETE: Limpiar Datos)
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    try {
        // ACCIÓN: DELETE (Limpiar) ¡CORREGIDO! Solo usamos 3 columnas.
        $sql_delete = 'UPDATE users SET weight = NULL, height = NULL, intolerances_allergies = NULL WHERE id = :user_id';
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_delete->execute();

        $msg_success = "Datos de salud eliminados (limpiados) con éxito.";
        header("Location: paciente_perfil_salud_CRUD.php?status=success&msg=" . urlencode($msg_success));
        exit();

    } catch (PDOException $e) {
        $msg = urlencode("Error al intentar eliminar los datos: " . $e->getMessage());
        header("Location: paciente_perfil_salud_CRUD.php?status=error&msg=" . $msg);
        exit();
    }
}

// 4. MANEJO DE LECTURA (READ y Preparación de la Vista)
try {
    // Lee directamente de la tabla 'users'
    // ¡CORREGIDO! Solo seleccionamos 3 columnas y usamos AS para renombrar
    $sql_read = 'SELECT weight AS peso, height AS altura, intolerances_allergies AS intolerancias FROM users WHERE id = :user_id';
    $stmt_read = $pdo->prepare($sql_read);
    $stmt_read->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_read->execute();
    $health_data = $stmt_read->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Muestra el detalle real del error solo para depuración
    $msg = "Error al cargar datos de salud. Detalles: " . $e->getMessage();
    $status = 'error';
    error_log("Error de BD al leer datos de salud: " . $e->getMessage());
}

// Determinar el estado de los datos
$has_data = !empty($health_data['peso']);
$current_action = 'update';

// 5. Incluir la Vista
require 'paciente_perfil_salud_vista.php';
?>
<?php
header('Content-Type: application/json');
require_once 'conexion.php'; // incluimos la conexión

$data = json_decode(file_get_contents('php://input'), true);

$nombre = $data['nombre'] ?? '';
$descripcion = $data['descripcion'] ?? '';
$detalle = $data['detalle'] ?? [];

if(!$nombre || empty($detalle)){
    echo json_encode(['success'=>false, 'mensaje'=>'Datos incompletos']);
    exit;
}

try {
    // Insertar plan
    $stmt = $pdo->prepare("INSERT INTO planes (nombre, descripcion) VALUES (?, ?)");
    $stmt->execute([$nombre, $descripcion]);
    $plan_id = $pdo->lastInsertId();

    // Insertar detalle
    $stmtDet = $pdo->prepare("INSERT INTO planes_detalle (plan_id, dia, comida, alimento_id, porcion) VALUES (?, ?, ?, ?, ?)");
    foreach($detalle as $item){
        $stmtDet->execute([$plan_id, $item['dia'], $item['comida'], $item['alimento_id'], $item['porcion']]);
    }

    echo json_encode(['success'=>true]);

} catch(Exception $e){
    echo json_encode(['success'=>false, 'mensaje'=>$e->getMessage()]);
}


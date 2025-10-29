<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['paciente_id'], $data['plan_id'])){
    echo json_encode(['success'=>false,'mensaje'=>'Datos incompletos']);
    exit;
}

$pacienteId = $data['paciente_id'];
$planId = $data['plan_id'];

try{
    $stmt = $pdo->prepare("INSERT INTO paciente_planes (paciente_id, plan_id, asignado_en) VALUES (?, ?, NOW())");
    $stmt->execute([$pacienteId, $planId]);

    echo json_encode(['success'=>true]);

} catch(Exception $e){
    echo json_encode(['success'=>false,'mensaje'=>$e->getMessage()]);
}
?>

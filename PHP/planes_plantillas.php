<?php
header('Content-Type: application/json');
require_once 'conexion.php'; // Debe definir $pdo

$method = $_SERVER['REQUEST_METHOD'];

if($method == 'GET') {
    try {
        // Obtener planes
        $stmt = $pdo->query("SELECT * FROM planes ORDER BY creado_en DESC");
        $planes = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $plan_id = $row['id'];

            // Obtener detalle
            $stmtDet = $pdo->prepare("SELECT dia, comida, alimento_id, porcion FROM planes_detalle WHERE plan_id = ?");
            $stmtDet->execute([$plan_id]);
            $detalle = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            $planes[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'creado_en' => $row['creado_en'],
                'detalle' => $detalle
            ];
        }

        echo json_encode($planes);
    } catch (Exception $e) {
        echo json_encode(['success'=>false, 'mensaje'=>$e->getMessage()]);
    }
}


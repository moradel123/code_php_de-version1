<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');

require_once 'db_connect.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data['id_rdv'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$id_rdv = intval($data['id_rdv']);

try {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->execute([$id_rdv]);

    if ($stmt->rowCount()) {
        echo json_encode(['success' => true, 'message' => 'RDV supprimé']);
    } else {
        echo json_encode(['success' => false, 'message' => 'RDV non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur suppression', 'error' => $e->getMessage()]);
}

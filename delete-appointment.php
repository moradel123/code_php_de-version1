<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include("db_connect.php");

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    echo json_encode(["error" => "Requête invalide ou corps JSON manquant."]);
    exit;
}

$id = $data["id"] ?? null;
$patientId = isset($data["patientId"]) ? intval($data["patientId"]) : null;

if (!$id || !$patientId) {
    echo json_encode(["error" => "Champs requis manquants."]);
    exit;
}

try {
    // Vérifier que le rendez-vous appartient bien au patient avant suppression
    $checkStmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
    $checkStmt->execute([$id, $patientId]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(["error" => "Rendez-vous non trouvé ou non autorisé"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Rendez-vous supprimé avec succès"]);
    } else {
        echo json_encode(["error" => "Échec de la suppression"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>
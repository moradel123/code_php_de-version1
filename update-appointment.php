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
$date = $data["date"] ?? null;
$time = $data["time"] ?? null;
$location = $data["location"] ?? null;
$patientId = isset($data["patientId"]) ? intval($data["patientId"]) : null;

if (!$id || !$date || !$time || !$location || !$patientId) {
    echo json_encode(["error" => "Tous les champs sont requis."]);
    exit;
}

try {
    // Vérifier que le rendez-vous appartient bien au patient avant modification
    $checkStmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
    $checkStmt->execute([$id, $patientId]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(["error" => "Rendez-vous non trouvé ou non autorisé"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE appointments SET date = ?, time = ?, location = ? WHERE id = ?");
    $stmt->execute([$date, $time, $location, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Rendez-vous mis à jour avec succès"]);
    } else {
        echo json_encode(["error" => "Aucune modification effectuée"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>
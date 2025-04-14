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

$patientId = isset($data["patientId"]) ? intval($data["patientId"]) : null;

if (!$patientId) {
    echo json_encode(["error" => "ID du patient manquant."]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY date, time");
    $stmt->execute([$patientId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "appointments" => $appointments]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Optionnel, pour le test local
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include("db_connect.php"); // Assurez-vous que ce fichier contient bien la connexion PDO

// Lire le corps JSON
$data = json_decode(file_get_contents("php://input"), true);

// Vérifier le JSON
if (json_last_error() !== JSON_ERROR_NONE || !$data) {
    echo json_encode(["error" => "Requête invalide ou corps JSON manquant."]);
    exit;
}

// Extraire les données
$date = $data["date"] ?? null;
$time = $data["time"] ?? null;
$location = $data["location"] ?? null;
$patientId = isset($data["patientId"]) ? intval($data["patientId"]) : null;

// Vérifier que tout est fourni
if (!$date || !$time || !$location || !$patientId) {
    echo json_encode(["error" => "Champs requis manquants."]);
    exit;
}
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE date = ? AND time = ? AND location = ?");
$stmt->execute([$date, $time, $location]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    echo json_encode(["error" => "Ce créneau est déjà réservé."]);
    exit;
}
try {
    // Vérifier si le créneau est déjà réservé
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE date = ? AND time = ? AND location = ?");
    $stmt->execute([$date, $time, $location]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(["error" => "Ce créneau est déjà réservé."]);
        exit;
    }

    // Insérer le rendez-vous
    $stmt = $pdo->prepare("INSERT INTO appointments (date, time, location, patient_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$date, $time, $location, $patientId]);

    echo json_encode(["message" => "Rendez-vous réservé avec succès."]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>

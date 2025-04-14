<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['patientId'])) {
        throw new Exception('ID patient manquant');
    }

    $patientId = $data['patientId'];

    // Récupérer les infos du patient
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception('Patient non trouvé');
    }

    $response = [
        'success' => true,
        'patient' => $patient
    ];

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données';
    error_log('PDOException: ' . $e->getMessage());
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>
<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

session_start();

// Enable CORS
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        throw new Exception('Non autorisé', 401);
    }

    $userId = $_SESSION['user']['id'];

    // Récupérer les infos du patient
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$userId]);
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
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response);
?>
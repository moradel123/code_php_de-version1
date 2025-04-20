<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);

// CORS headers
header("Access-Control-Allow-Origin: http://localhost:5174"); // Corrigez le port si nécessaire
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
        throw new Exception('Vous devez être connecté pour voir vos rendez-vous', 401);
    }

    // Récupérer les données POST
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides', 400);
    }

    // Utiliser l'ID de l'utilisateur de la session plutôt que des données POST
    $patientId = $_SESSION['user']['id'];

    // Préparer et exécuter la requête pour récupérer les rendez-vous
    $stmt = $pdo->prepare("SELECT id, date, time, location FROM appointments WHERE patient_id = ? ORDER BY date, time");
    $stmt->execute([$patientId]);
    
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'message' => 'Rendez-vous récupérés avec succès',
        'appointments' => $appointments
    ];

} catch (PDOException $e) {
    $response['message'] = 'Erreur de base de données : ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response);
?>
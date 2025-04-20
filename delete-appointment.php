<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5174"); // Remplacez par votre URL frontend
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once 'db_connect.php';

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        throw new Exception('Non authentifié', 401);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE || !$data) {
        throw new Exception('Données invalides', 400);
    }

    if (empty($data['id'])) {
        throw new Exception('ID de rendez-vous manquant', 400);
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    $patientId = $_SESSION['user']['id']; // Utiliser l'ID de la session

    // Vérifier que le rendez-vous appartient au patient
    $checkStmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
    $checkStmt->execute([$id, $patientId]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Rendez-vous non trouvé ou non autorisé', 403);
    }

    // Supprimer le rendez-vous
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND patient_id = ?");
    $stmt->execute([$id, $patientId]);

    if ($stmt->rowCount() > 0) {
        $response = [
            'success' => true,
            'message' => 'Rendez-vous supprimé avec succès'
        ];
    } else {
        throw new Exception('Échec de la suppression', 400);
    }
} catch (PDOException $e) {
    $response['message'] = 'Erreur de base de données : ' . $e->getMessage();
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response);
?>
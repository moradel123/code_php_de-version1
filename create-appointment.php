<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

session_start();

$response = ['success' => false];

try {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        throw new Exception('Vous devez être connecté pour prendre un rendez-vous', 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE || !$data) {
        throw new Exception('Données invalides', 400);
    }

    // Valider les champs requis
    $requiredFields = ['date', 'time', 'location', 'patientId'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Le champ $field est requis", 400);
        }
    }

    // Vérifier que patientId correspond à l'utilisateur connecté
    if ($data['patientId'] !== $_SESSION['user']['id']) {
        throw new Exception('Action non autorisée', 403);
    }

    // Valider le format de la date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
        throw new Exception('Format de date invalide', 400);
    }

    // Valider le format de l'heure
    if (!preg_match('/^\d{2}:\d{2}$/', $data['time'])) {
        throw new Exception('Format de l\'heure invalide', 400);
    }

    // Vérifier si le créneau est déjà réservé
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE date = ? AND time = ? AND location = ?");
    $stmt->execute([$data['date'], $data['time'], $data['location']]);
    if ($stmt->fetch()) {
        throw new Exception('Ce créneau est déjà réservé', 400);
    }

    // Insérer le rendez-vous
    $stmt = $pdo->prepare("INSERT INTO appointments (date, time, location, patient_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['date'], $data['time'], $data['location'], $data['patientId']]);

    $response = [
        'success' => true,
        'message' => 'Rendez-vous pris avec succès',
        'appointmentId' => $pdo->lastInsertId()
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
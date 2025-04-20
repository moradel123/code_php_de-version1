<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

// Configuration CORS sécurisée
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:5174'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // Pour les requêtes sans Origin header (comme les requêtes directes)
    header("Access-Control-Allow-Origin: null");
}

// Headers essentiels pour les requêtes préflight
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN");
header("Access-Control-Max-Age: 3600");
header("Vary: Origin");

// Gestion des requêtes OPTIONS (préflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Démarrer la session après les headers CORS
session_start();

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier que c'est une requête GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Méthode non autorisée', 405);
    }

    // Récupérer l'ID patient
    $patientId = $_GET['patientId'] ?? null;
    
    if (!$patientId) {
        throw new Exception('Paramètre patientId manquant', 400);
    }

    if (!is_numeric($patientId)) {
        throw new Exception('ID patient doit être numérique', 400);
    }

    // Vérification de l'authentification
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Authentification requise', 401);
    }

    // Vérifier que l'utilisateur a accès à ces données
    if ($_SESSION['user_id'] != $patientId && $_SESSION['role'] !== 'admin') {
        throw new Exception('Accès non autorisé', 403);
    }

    // Requête sécurisée
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email,cin FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception('Patient non trouvé', 404);
    }

    $response = [
        'success' => true,
        'patient' => $patient
    ];

} catch (PDOException $e) {
    error_log('PDO Error: ' . $e->getMessage());
    $response['message'] = 'Erreur de base de données';
    http_response_code(500);
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
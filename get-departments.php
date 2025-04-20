<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

try {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    // Pour développement seulement
    if (empty($authHeader)) {
        error_log("Avertissement: Authorization header manquant - Mode développement activé");
    }

    $stmt = $pdo->query("SELECT id, nom FROM departements");
    
    echo json_encode([
        'success' => true,
        'departments' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
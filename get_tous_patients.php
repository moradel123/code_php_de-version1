<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Répondre immédiatement aux requêtes OPTIONS (pré-vol)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

try {
    // Vérification plus souple de l'authentification pour le développement
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    // Pour développement seulement - à retirer en production
    if (empty($authHeader)) {
        error_log("Avertissement: Authorization header manquant - Mode développement activé");
    }

    $stmt = $pdo->query("
        SELECT 
            id, nom, prenom, cin, gender, age, email,
            numero_Tele as telephone, adress as adresse, pb, 
            doctor_traitant as docteur,
            created_at, updated_at
        FROM patients
    ");
    
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'patients' => $patients
    ]);
    
} catch (PDOException $e) {
    // Code d'erreur SQL standard
    $code = is_numeric($e->getCode()) ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    // Code d'erreur générique
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>
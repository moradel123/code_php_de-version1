<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Vérification de l'en-tête d'autorisation
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        throw new Exception('Token d\'authentification manquant');
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token = str_replace('Bearer ', '', $authHeader);

    // Décodage simplifié du token JWT
    $tokenParts = explode('.', $token);
    if (count($tokenParts) !== 3) {
        throw new Exception('Format de token invalide');
    }

    $payload = json_decode(base64_decode($tokenParts[1]), true);
    if (!$payload || !isset($payload['email'])) {
        throw new Exception('Token invalide');
    }

    $email = $payload['email'];

    // Récupération des données du patient
    $stmt = $pdo->prepare("SELECT 
        nom, prenom, email, numero_Tele, cin, gender, 
        adress, pb, doctor_traitant,
        IFNULL(date_naissance, 'Non spécifiée') as date_naissance,
        IFNULL(date_inscription, 'Non spécifiée') as date_inscription
        FROM patients WHERE email = ?");
    $stmt->execute([$email]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Patient non trouvé']);
        exit;
    }

    // Formatage des dates si elles existent
    if ($patient['date_naissance'] !== 'Non spécifiée') {
        $patient['date_naissance'] = date('d/m/Y', strtotime($patient['date_naissance']));
    }
    if ($patient['date_inscription'] !== 'Non spécifiée') {
        $patient['date_inscription'] = date('d/m/Y', strtotime($patient['date_inscription']));
    }

    // Réponse avec les données du patient
    echo json_encode([
        'success' => true,
        'data' => $patient
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
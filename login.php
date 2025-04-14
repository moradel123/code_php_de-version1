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

// Enable CORS
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email et mot de passe requis');
    }

    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    
    // Recherche dans la table patients
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data['password'], $user['password'])) {
        usleep(rand(200000, 500000));
        throw new Exception('Identifiants incorrects');
    }

    // Stocker les infos utilisateur en session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'nom' => $user['nom'],
        'prenom' => $user['prenom'],
        'role' => 'patient'
    ];

    // Réponse avec les données utilisateur
    $response = [
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'cin' => $user['cin'],
            'numero_Tele' => $user['numero_Tele'],
            'adress' => $user['adress'],
            'pb' => $user['pb'],
            'doctor_traitant' => $user['doctor_traitant'],
            'role' => 'patient'
        ]
    ];

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données';
    error_log('PDOException: ' . $e->getMessage());
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(401);
}

echo json_encode($response);
?>
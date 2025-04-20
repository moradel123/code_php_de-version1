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

// CORS
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
    // Comptes par défaut à créer
    $defaultAccounts = [
        'admin' => [
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'nom' => 'Admin',
            'prenom' => 'System',
            'role' => 'admin',
            'cin' => 'A000000',
            'numero_Tele' => '0600000000',
            'adress' => 'Siège social',
            'pb' => 'Administration système',
            'doctor_traitant' => null
        ],
        'directeur' => [
            'email' => 'directeur@example.com',
            'password' => password_hash('directeur123', PASSWORD_DEFAULT),
            'nom' => 'Directeur',
            'prenom' => 'Général',
            'role' => 'directeur',
            'cin' => 'D000000',
            'numero_Tele' => '0600000001',
            'adress' => 'Direction',
            'pb' => 'Gestion générale',
            'doctor_traitant' => null
        ]
    ];

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email et mot de passe requis');
    }

    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

    // Vérifier si c'est un compte par défaut
    $isDefaultAccount = false;
    $defaultAccountType = null;
    
    foreach ($defaultAccounts as $type => $account) {
        if ($email === $account['email']) {
            $isDefaultAccount = true;
            $defaultAccountType = $type;
            break;
        }
    }

    if ($isDefaultAccount) {
        // Vérifier si le compte par défaut existe déjà
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Créer le compte par défaut s'il n'existe pas
            $account = $defaultAccounts[$defaultAccountType];
            $insertStmt = $pdo->prepare("
                INSERT INTO patients 
                (email, password, nom, prenom, role, cin, numero_Tele, adress, pb, doctor_traitant, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $insertStmt->execute([
                $account['email'],
                $account['password'],
                $account['nom'],
                $account['prenom'],
                $account['role'],
                $account['cin'],
                $account['numero_Tele'],
                $account['adress'],
                $account['pb'],
                $account['doctor_traitant']
            ]);
            
            // Récupérer le nouvel utilisateur
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else {
        // Compte normal - vérification standard
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérification du mot de passe
    if (!$user || !password_verify($data['password'], $user['password'])) {
        usleep(rand(200000, 500000)); // anti bruteforce
        throw new Exception('Email ou mot de passe incorrect');
    }

    // Connexion réussie
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'nom' => $user['nom'],
        'prenom' => $user['prenom'],
        'role' => $user['role']
    ];

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
            'role' => $user['role']
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
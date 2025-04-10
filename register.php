<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$response = ['success' => false, 'message' => '', 'user' => null];

// Validation des données
if (empty($data['name']) || empty($data['email']) || empty($data['password']) || 
    empty($data['tel']) || empty($data['address']) || empty($data['cin'])) {
    $response['message'] = 'Tous les champs sont obligatoires';
    echo json_encode($response);
    exit;
}

// Vérification si l'email existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
if ($stmt->fetch()) {
    $response['message'] = 'Cet email est déjà utilisé';
    echo json_encode($response);
    exit;
}

// Hash du mot de passe
$hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

// Insertion dans la base de données
try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, tel, address, cin, role) 
                          VALUES (?, ?, ?, ?, ?, ?, 'user')");
    $stmt->execute([
        $data['name'],
        $data['email'],
        $hashedPassword,
        $data['tel'],
        $data['address'],
        $data['cin']
    ]);

    // Récupérer l'ID de l'utilisateur nouvellement créé
    $userId = $pdo->lastInsertId();

    // Préparer les données utilisateur à retourner
    $response['user'] = [
        'id' => $userId,
        'name' => $data['name'],
        'email' => $data['email'],
        'tel' => $data['tel'],
        'address' => $data['address'],
        'cin' => $data['cin'],
        'role' => 'user'
    ];

    $response['success'] = true;
    $response['message'] = 'Inscription réussie';
} catch (PDOException $e) {
    $response['message'] = 'Erreur lors de l\'inscription: ' . $e->getMessage();
}

echo json_encode($response);
?>
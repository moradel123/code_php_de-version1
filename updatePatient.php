<?php
header("Content-Type: application/json");
require_once 'db_connect.php';

session_start();

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
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        throw new Exception('Non autorisé', 401);
    }

    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }

    $userId = $_SESSION['user']['id'];

    // Préparer la requête de mise à jour
    $sql = "UPDATE patients SET 
            prenom = :prenom,
            nom = :nom,
            email = :email,
            numero_Tele = :phone,
            cin = :cin,
            gender = :gender,
            adress = :address,
            pb = :pb,
            doctor_traitant = :doctor,
            age = :age
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    // Extraire le prénom et nom du champ fullname si nécessaire
    $names = explode(' ', $input['name'] ?? '', 2);
    $prenom = $names[0] ?? '';
    $nom = $names[1] ?? '';

    $params = [
        ':prenom' => $prenom,
        ':nom' => $nom,
        ':email' => $input['email'] ?? '',
        ':phone' => $input['tel'] ?? '',
        ':cin' => $input['cin'] ?? '',
        ':gender' => $input['gender'] ?? '',
        ':address' => $input['address'] ?? '',
        ':pb' => $input['pb'] ?? '',
        ':doctor' => $input['doctor_traitant'] ?? '',
        ':age' => $input['age'] ?? '', // Added age parameter
        ':id' => $userId
    ];

    if ($stmt->execute($params)) {
        $response = [
            'success' => true,
            'message' => 'Profil mis à jour avec succès'
        ];
    } else {
        throw new Exception('Échec de la mise à jour');
    }

} catch (PDOException $e) {
    $response['message'] = 'Erreur base de données: ' . $e->getMessage();
    error_log('PDOException: ' . $e->getMessage());
    http_response_code(500);
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code($e->getCode() ?: 400);
}

echo json_encode($response);
?>
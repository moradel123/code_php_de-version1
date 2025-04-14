<?php
header("Access-Control-Allow-Origin: http://localhost:5174");

header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gérer la requête OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Connexion à la base de données
$host = "localhost";
$db_name = "hospital";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données."]);
    exit;
}

// Récupérer les données JSON
$data = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données JSON invalides ou manquantes."]);
    exit;
}

// Liste des champs requis
$requiredFields = ["nom", "prenom", "cin", "email", "numero_Tele", "password"];

// Vérifier les champs requis
$errors = [];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        $errors[$field] = "Ce champ est requis.";
    }
}

// Valider uniquement si le champ existe et n'est pas vide
if (isset($data["email"]) && $data["email"] !== '' && !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Email invalide.";
}

// Valider le mot de passe seulement s'il est présent
if (isset($data["password"]) && strlen($data["password"]) < 8) {
    $errors["password"] = "Le mot de passe doit contenir au moins 8 caractères.";
}

// Vérifier l'unicité du CIN et de l'email seulement s'ils sont valides
if (!isset($errors["cin"]) && isset($data["cin"])) {
    $cin = $conn->real_escape_string($data["cin"]);
    $email = $conn->real_escape_string($data["email"]);
    
    $checkQuery = "SELECT * FROM patients WHERE cin = '$cin' OR email = '$email'";
    $result = $conn->query($checkQuery);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row["cin"] === $cin) {
                $errors["cin"] = "Ce CIN est déjà utilisé.";
            }
            if ($row["email"] === $email) {
                $errors["email"] = "Cet email est déjà utilisé.";
            }
        }
    }
}

// S'il y a des erreurs, on les retourne
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "errors" => $errors,
        "message" => "Erreur de validation."
    ]);
    exit;
}

// Hasher le mot de passe
$hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);

// Préparer la requête d'insertion
$stmt = $conn->prepare("
    INSERT INTO patients (
        nom, prenom, cin, gender, age, email, 
        numero_Tele, adress, pb, doctor_traitant, password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de préparation de la requête: " . $conn->error]);
    exit;
}

// Gérer les valeurs optionnelles
$gender = isset($data["gender"]) ? $data["gender"] : null;
$age = isset($data["age"]) ? $data["age"] : null;
$adress = isset($data["adress"]) ? $data["adress"] : null;
$pb = isset($data["pb"]) ? $data["pb"] : null;
$doctor_traitant = isset($data["doctor_traitant"]) ? $data["doctor_traitant"] : null;

$stmt->bind_param(
    "ssssissssss",
    $data["nom"],
    $data["prenom"],
    $data["cin"],
    $gender,
    $age,
    $data["email"],
    $data["numero_Tele"],
    $adress,
    $pb,
    $doctor_traitant,
    $hashedPassword
);

// Exécuter et vérifier
if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Compte créé avec succès."]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de l'enregistrement: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
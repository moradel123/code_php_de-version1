<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli('localhost', 'root', '', 'hospital'); // Update with your database details

if ($conn->connect_error) {
    die(json_encode(["error" => "Erreur de connexion à la base de données."]));
}

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$token = $data['code'] ?? '';
$newPassword = $data['newPassword'] ?? '';

// Fixed code for validation
$expectedToken = "123456"; // Same fixed code as in forgot-password.php

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($token) || empty($newPassword)) {
    echo json_encode(["error" => "Données invalides."]);
    exit;
}

// Validate the reset code
if ($token !== $expectedToken) {
    echo json_encode(["error" => "Code de réinitialisation incorrect."]);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Update the password in the users table
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute()) {
    echo json_encode(["message" => "Mot de passe réinitialisé avec succès."]);
} else {
    echo json_encode(["error" => "Erreur lors de la réinitialisation du mot de passe."]);
}

$conn->close();
?>

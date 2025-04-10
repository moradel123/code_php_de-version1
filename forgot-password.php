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

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Email invalide."]);
    exit;
}

// Check if email exists in the database
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["error" => "Email introuvable."]);
    exit;
}

// Generate a fixed reset code (for testing purposes)
$resetToken = "123456"; // Fixed code for simplicity
$expiresAt = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Save the reset code to the database
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $resetToken, $expiresAt);

if ($stmt->execute()) {
    // Return the reset code in the response (for testing only)
    echo json_encode(["message" => "Code de réinitialisation généré.", "code" => $resetToken]);
} else {
    echo json_encode(["error" => "Erreur lors de la génération du code."]);
}

$conn->close();
?>

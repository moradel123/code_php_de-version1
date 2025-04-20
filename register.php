<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$host = "localhost";
$db_name = "hospital";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Database connection error: " . $conn->connect_error
    ]);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "message" => "Invalid JSON: " . json_last_error_msg()
    ]);
    exit;
}

if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "message" => "No data received"
    ]);
    exit;
}

// Required fields
$requiredFields = ["nom", "prenom", "cin", "email", "numero_Tele", "password"];
$errors = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $errors[$field] = "This field is required";
    }
}

// Validate email format
if (!empty($data["email"]) && !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Invalid email format";
}

// Validate password length
if (!empty($data["password"]) && strlen($data["password"]) < 8) {
    $errors["password"] = "Password must be at least 8 characters";
}

// Check for unique CIN and email
if (empty($errors["cin"]) && empty($errors["email"])) {
    $cin = $conn->real_escape_string($data["cin"]);
    $email = $conn->real_escape_string($data["email"]);
    
    $checkQuery = "SELECT id FROM patients WHERE cin = '$cin' OR email = '$email' LIMIT 1";
    $result = $conn->query($checkQuery);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (isset($row["cin"]) && $row["cin"] === $data["cin"]) {
            $errors["cin"] = "CIN already exists";
        }
        if (isset($row["email"]) && $row["email"] === $data["email"]) {
            $errors["email"] = "Email already exists";
        }
    }
}

// Return errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Validation failed",
        "errors" => $errors
    ]);
    exit;
}

// Hash password
$hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);

// Prepare SQL statement
$stmt = $conn->prepare("
    INSERT INTO patients (
        nom, prenom, cin, gender, age, email, 
        numero_Tele, adress, pb, doctor_traitant, password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Prepare failed: " . $conn->error
    ]);
    exit;
}

// Bind parameters
$gender = $data["gender"] ?? null;
$age = $data["age"] ?? null;
$adress = $data["adress"] ?? null;
$pb = $data["pb"] ?? null;
$doctor_traitant = $data["doctor_traitant"] ?? null;

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

// Execute statement
if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        "success" => true, 
        "message" => "Account created successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "message" => "Execution failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
<?php
// Allow access from your frontend server
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: http://localhost:5174");
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0); // Handle preflight requests
}

header('Content-Type: application/json');

// Read the JSON input
$inputData = json_decode(file_get_contents("php://input"), true);


if (!isset($inputData['category']) || empty($inputData['category'])) {
    echo json_encode(["error" => "Category is missing."]);
    exit;
}

$category = $inputData['category'];

// Allowed categories
$allowed_categories = ['doctors', 'infermiers', 'patients', 'departements'];
if (!in_array($category, $allowed_categories)) {
    echo json_encode(["error" => "Invalid category."]);
    exit;
}

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "hospital";

// Connect to the database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Function to check if email already exists
function checkEmailExists($conn, $email, $category)
{
    $email = $conn->real_escape_string($email);
    $table = '';

    // Determine the table based on category
    switch ($category) {
        case 'doctors':
            $table = 'doctors';
            break;
        case 'infermiers':
            $table = 'infermiers';
            break;
        case 'patients':
            $table = 'patients';
            break;
        case 'departements':
            $table = 'departements';
            break;
        default:
            return false; // If category is invalid
    }

    // Query to check if email exists in the respective table
    $sql = "SELECT * FROM $table WHERE email = '$email'";
    $result = $conn->query($sql);

    return $result->num_rows > 0; // Returns true if email exists
}

// Switch case to insert data into the correct table
switch ($category) {
    case 'doctors':
        if (isset($inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['salary'], $inputData['departement'])) {

            // Check if email already exists
            if (checkEmailExists($conn, $inputData['email'], 'doctors')) {
                echo json_encode(["error" => "Email already exists for doctor."]);
                exit;
            }

            // Prepare the SQL statement to insert the doctor
            $stmt = $conn->prepare("INSERT INTO doctors (id, nom, prenom, pb, age, numero_Tele, email, adress, gender, salary, departement) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssissdsss', $inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['salary'], $inputData['departement']);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Doctor added successfully."]);
            } else {
                echo json_encode(["error" => "Error: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Missing required fields for doctors."]);
        }
        break;

    case 'infermiers':
        if (isset($inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['salary'], $inputData['departement'])) {

            // Check if email already exists
            if (checkEmailExists($conn, $inputData['email'], 'infermiers')) {
                echo json_encode(["error" => "Email already exists for infermier."]);
                exit;
            }

            // Prepare the SQL statement to insert the infermier
            $stmt = $conn->prepare("INSERT INTO infermiers (id, nom, prenom, pb, age, numero_Tele, email, adress, gender, salary, departement) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssissdsss', $inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['salary'], $inputData['departement']);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Infermier added successfully."]);
            } else {
                echo json_encode(["error" => "Error: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Missing required fields for infermiers."]);
        }
        break;

    case 'patients':
        if (isset($inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['doctor_traitant'], $inputData['rendezVous'])) {

            // Check if email already exists
            if (checkEmailExists($conn, $inputData['email'], 'patients')) {
                echo json_encode(["error" => "Email already exists for patient."]);
                exit;
            }

            // Prepare the SQL statement to insert the patient
            $stmt = $conn->prepare("INSERT INTO patients (id, nom, prenom, pb, age, numero_Tele, email, adress, gender, doctor_traitant, rendezVous) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssissdsss', $inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['doctor_traitant'], $inputData['rendezVous']);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Patient added successfully."]);
            } else {
                echo json_encode(["error" => "Error: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Missing required fields for patients."]);
        }
        break;

    case 'departements':
        if (isset($inputData['id'], $inputData['nom'], $inputData['totDoc'],  $inputData['totInf'],  $inputData['totPat'])) {

            // Prepare the SQL statement to insert the departement
            $stmt = $conn->prepare("INSERT INTO departements (id, nom, totDoc, totInf, totPat) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('isiii', $inputData['id'], $inputData['nom'], $inputData['totDoc'], $inputData['totInf'], $inputData['totPat']);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Departement added successfully."]);
            } else {
                echo json_encode(["error" => "Error: " . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Missing required fields for departements."]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid category."]);
}

// Close the database connection
$conn->close();

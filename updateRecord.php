<?php
// Allow access from your frontend server (you can also allow all origins using "*")
header("Access-Control-Allow-Origin: *");  // Update this with your frontend URL
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");  // Allow the HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization");  // Allow specific headers (if required)

// Handle preflight requests (for OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);  // Respond with 200 OK for preflight
}

// Set the content type to JSON for response
header('Content-Type: application/json');

// Read the JSON input
$inputData = json_decode(file_get_contents("php://input"), true);

// Check if data is valid
if (isset($inputData['category'])) {
    $category = $inputData['category'];

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

    // Switch case to update data in the correct table
    switch ($category) {
        case 'doctors':
            if (isset($inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['salary'], $inputData['departement'])) {
                $id = (int) $inputData['id'];
                $nom = $conn->real_escape_string($inputData['nom']);
                $prenom = $conn->real_escape_string($inputData['prenom']);
                $pb = $conn->real_escape_string($inputData['pb']);
                $age = (int) $inputData['age'];
                $numero_Tele = $conn->real_escape_string($inputData['numero_Tele']);
                $email = $conn->real_escape_string($inputData['email']);
                $adress = $conn->real_escape_string($inputData['adress']);
                $gender = $conn->real_escape_string($inputData['gender']);
                $salary = (float) $inputData['salary'];
                $departement = $conn->real_escape_string($inputData['departement']);

                $sql = "UPDATE doctors SET nom='$nom', prenom='$prenom', pb='$pb', age=$age, numero_Tele='$numero_Tele', email='$email', adress='$adress', gender='$gender', salary=$salary, departement='$departement' WHERE id=$id";

                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["message" => "Doctor updated successfully."]);
                } else {
                    echo json_encode(["error" => "Error: " . $conn->error]);
                }
            } else {
                echo json_encode(["error" => "Missing required fields for doctors."]);
            }
            break;

        case 'infermiers':
            if (isset($inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['salary'], $inputData['departement'])) {
                $id = (int) $inputData['id'];
                $nom = $conn->real_escape_string($inputData['nom']);
                $prenom = $conn->real_escape_string($inputData['prenom']);
                $pb = $conn->real_escape_string($inputData['pb']);
                $age = (int) $inputData['age'];
                $numero_Tele = $conn->real_escape_string($inputData['numero_Tele']);
                $email = $conn->real_escape_string($inputData['email']);
                $adress = $conn->real_escape_string($inputData['adress']);
                $gender = $conn->real_escape_string($inputData['gender']);
                $salary = (float) $inputData['salary'];
                $departement = $conn->real_escape_string($inputData['departement']);

                $sql = "UPDATE infermiers SET nom='$nom', prenom='$prenom', pb='$pb', age=$age, numero_Tele='$numero_Tele', email='$email', adress='$adress', gender='$gender', salary=$salary, departement='$departement' WHERE id=$id";

                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["message" => "Infermier updated successfully."]);
                } else {
                    echo json_encode(["error" => "Error: " . $conn->error]);
                }
            } else {
                echo json_encode(["error" => "Missing required fields for infermiers."]);
            }
            break;

        case 'patients':
            if (isset($inputData['id'], $inputData['nom'], $inputData['prenom'], $inputData['pb'], $inputData['age'], $inputData['numero_Tele'], $inputData['email'], $inputData['adress'], $inputData['gender'], $inputData['doctor_traitant'], $inputData['rendezVous'])) {
                $id = (int) $inputData['id'];
                $nom = $conn->real_escape_string($inputData['nom']);
                $prenom = $conn->real_escape_string($inputData['prenom']);
                $pb = $conn->real_escape_string($inputData['pb']);
                $age = (int) $inputData['age'];
                $numero_Tele = $conn->real_escape_string($inputData['numero_Tele']);
                $email = $conn->real_escape_string($inputData['email']);
                $adress = $conn->real_escape_string($inputData['adress']);
                $gender = $conn->real_escape_string($inputData['gender']);
                $doctor_traitant = $conn->real_escape_string($inputData['doctor_traitant']);
                $rendezVous = $conn->real_escape_string($inputData['rendezVous']);

                $sql = "UPDATE patients SET nom='$nom', prenom='$prenom', pb='$pb', age=$age, numero_Tele='$numero_Tele', email='$email', adress='$adress', gender='$gender', doctor_traitant='$doctor_traitant', rendezVous='$rendezVous' WHERE id=$id";

                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["message" => "Patient updated successfully."]);
                } else {
                    echo json_encode(["error" => "Error: " . $conn->error]);
                }
            } else {
                echo json_encode(["error" => "Missing required fields for patients."]);
            }
            break;

        case 'departements':
            if (isset($inputData['id'], $inputData['nom'], $inputData['totDoc'], $inputData['totInf'], $inputData['totPat'])) {
                // Assuming $inputData contains the form data
                $id = (int) $inputData['id'];
                $nom = $conn->real_escape_string($inputData['nom']); // Escape special characters
                $totDoc = (int) $inputData['totDoc']; // Convert to integer
                $totInf = (int) $inputData['totInf']; // Convert to integer
                $totPat = (int) $inputData['totPat']; // Convert to integer

                // Build the SQL query, ensuring all string values are properly quoted and escaped
                $sql = "UPDATE departements SET nom='$nom', totDoc=$totDoc, totInf=$totInf, totPat=$totPat WHERE id=$id";

                // Execute the query
                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["message" => "Departement updated successfully."]);
                } else {
                    echo json_encode(["error" => "Error: " . $conn->error]);
                }
            } else {
                echo json_encode(["error" => "Missing required fields for departements."]);
            }
            break;

        default:
            echo json_encode(["error" => "Invalid category."]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(["error" => "Category is missing."]);
}

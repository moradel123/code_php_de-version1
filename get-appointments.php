<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Connexion à la base de données
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$dbpassword = ""; // Remplacez par votre mot de passe MySQL
$dbname = "hospital";

$conn = new mysqli($servername, $username, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = 1; // Remplacez par l'ID de l'utilisateur connecté (à récupérer depuis la session ou le token)

// Récupérer les rendez-vous de l'utilisateur
$sql = "SELECT * FROM appointments WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $appointments = array();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    echo json_encode(array("appointments" => $appointments));
} else {
    echo json_encode(array("message" => "Aucun rendez-vous trouvé."));
}

$conn->close();
?>
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "hospital";

// Connect to database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$sql = "SELECT id, nom, prenom, pb, age, email, departement FROM doctors";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    echo json_encode(["doctors" => $doctors]);
} else {
    echo json_encode(["doctors" => []]);
}

$conn->close();
?>
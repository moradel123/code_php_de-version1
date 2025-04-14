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

// Fetch departments
$sql = "SELECT id, nom FROM departements";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $departments = [];
    while($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    echo json_encode(["departments" => $departments]);
} else {
    echo json_encode(["departments" => []]);
}

$conn->close();
?>
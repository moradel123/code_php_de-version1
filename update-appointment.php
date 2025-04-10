<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($data === null || !isset($data['id'], $data['date'], $data['time'], $data['location'])) {
    echo json_encode(array("message" => "Données invalides"));
    exit;
}

$id = $data['id'];
$date = $data['date'];
$time = $data['time'];
$location = $data['location'];

$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "hospital";

$conn = new mysqli($servername, $username, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "UPDATE appointments SET date = '$date', time = '$time', location = '$location' WHERE id = '$id'";
if ($conn->query($sql)) {
    echo json_encode(array("message" => "Rendez-vous mis à jour avec succès."));
} else {
    echo json_encode(array("message" => "Erreur lors de la mise à jour du rendez-vous."));
}

$conn->close();
?>
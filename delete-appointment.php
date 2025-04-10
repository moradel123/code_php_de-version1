<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($data === null || !isset($data['id'])) {
    echo json_encode(array("message" => "Données invalides"));
    exit;
}

$id = $data['id'];

$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "hospital";

$conn = new mysqli($servername, $username, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "DELETE FROM appointments WHERE id = '$id'";
if ($conn->query($sql)) {
    echo json_encode(array("message" => "Rendez-vous supprimé avec succès."));
} else {
    echo json_encode(array("message" => "Erreur lors de la suppression du rendez-vous."));
}

$conn->close();
?>
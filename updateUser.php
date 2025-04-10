<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !isset($data['id'], $data['name'], $data['email'], $data['tel'], $data['address'], $data['cin'])) {
    die(json_encode(["message" => "Données invalides"]));
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hospital";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["message" => "Échec de la connexion"]));
}

$sql = "UPDATE users SET name = ?, email = ?, tel = ?, address = ?, cin = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $data['name'], $data['email'], $data['tel'], $data['address'], $data['cin'], $data['id']);

if ($stmt->execute()) {
    echo json_encode(["message" => "Mise à jour réussie"]);
} else {
    echo json_encode(["message" => "Erreur lors de la mise à jour"]);
}

$stmt->close();
$conn->close();
?>
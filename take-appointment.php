<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($data === null || !isset($data['date'], $data['time'], $data['location'])) {
    echo json_encode(array("message" => "Données invalides"));
    exit;
}

$date = $data['date'];
$time = $data['time'];
$location = $data['location'];
$user_id = 1; // Remplacez par l'ID de l'utilisateur connecté

$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "hospital";

$conn = new mysqli($servername, $username, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_check = "SELECT * FROM appointments WHERE date = '$date' AND time = '$time' AND location = '$location'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    echo json_encode(array("message" => "Ce créneau est déjà pris."));
} else {
    $sql = "INSERT INTO appointments (user_id, date, time, location, status) VALUES ('$user_id', '$date', '$time', '$location', 'pending')";
    if ($conn->query($sql)) {
        echo json_encode(array("message" => "Rendez-vous pris avec succès."));
    } else {
        echo json_encode(array("message" => "Erreur lors de la prise de rendez-vous."));
    }
}

$conn->close();
?>
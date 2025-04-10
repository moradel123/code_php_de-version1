<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if ($data === null || !isset($data['date'], $data['location'])) {
    echo json_encode(array("message" => "Données invalides"));
    exit;
}

$date = $data['date'];
$location = $data['location'];

$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "hospital";

$conn = new mysqli($servername, $username, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT time FROM appointments WHERE date = '$date' AND location = '$location'";
$result = $conn->query($sql);

$taken_slots = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taken_slots[] = $row['time'];
    }
}

$available_slots = array();
for ($hour = 9; $hour <= 17; $hour++) {
    $time = sprintf("%02d:00:00", $hour);
    if (!in_array($time, $taken_slots)) {
        $available_slots[] = array("time" => $time, "status" => "متاح");
    }
}

echo json_encode(array("slots" => $available_slots));

$conn->close();
?>
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include("db_connect.php");

$data = json_decode(file_get_contents("php://input"), true);

// Validation des données
if (!$data || !isset($data['date']) || !isset($data['location']) || !isset($data['time'])) {
    echo json_encode([
        "success" => false,
        "error" => "Données manquantes: date, location et time sont requis"
    ]);
    exit;
}

$date = $data['date'];
$location = $data['location'];
$time = $data['time'];

try {
    // Vérifier si le créneau est déjà réservé
    $stmt = $pdo->prepare("SELECT id FROM appointments WHERE date = ? AND location = ? AND time = ?");
    $stmt->execute([$date, $location, $time]);
    $existingAppointment = $stmt->fetch();

    if ($existingAppointment) {
        echo json_encode([
            "success" => false,
            "alreadyReserved" => true,
            "message" => "Ce créneau horaire est déjà réservé pour cette date et ce lieu"
        ]);
    } else {
        // Si on veut aussi retourner tous les créneaux réservés
        $stmt = $pdo->prepare("SELECT time FROM appointments WHERE date = ? AND location = ?");
        $stmt->execute([$date, $location]);
        $reservedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode([
            "success" => true,
            "reserved" => $reservedTimes,
            "message" => "Créneau disponible"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Erreur lors de la vérification des créneaux: " . $e->getMessage()
    ]);
}
?>
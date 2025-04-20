                                                                                                                            <?php

header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: http://localhost:5174");
// Si c'est une requête OPTIONS, on renvoie un statut 200 (c'est un pré-vol)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Connexion à la base de données
include("db_connect.php");



try {
    $stmt = $pdo->query("
        SELECT 
            a.id,
            a.patient_id,
            a.date,
            a.time,
            a.location,
            a.description,
            p.nom AS patient_nom
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        ORDER BY a.date ASC
    ");

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($appointments);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de la récupération des rendez-vous",
        "error" => $e->getMessage()
]);
}

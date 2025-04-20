<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include("db_connect.php");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["error" => "Méthode non autorisée"]);
    exit;
}

try {
    // Query to count appointments grouped by month
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date, '%Y-%m') AS mois, 
            COUNT(*) AS rendezvous 
        FROM appointments 
        GROUP BY DATE_FORMAT(date, '%Y-%m') 
        ORDER BY DATE_FORMAT(date, '%Y-%m')
    ");
    $stmt->execute();
    $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "data" => $statistics]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>

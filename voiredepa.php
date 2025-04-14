<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

include("db_connect.php");

try {
    $stmt = $pdo->query("SELECT id, nom FROM departments ORDER BY nom");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["departments" => $departments]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur serveur : " . $e->getMessage()]);
}
?>
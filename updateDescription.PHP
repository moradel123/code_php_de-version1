<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Connexion PDO
require_once 'db_connect.php';

// Lire les données JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Debug
error_log("🔍 RAW JSON: $raw");
error_log("🔍 Data décodée: " . print_r($data, true));

// Vérification des données reçues
if (!isset($data['id_rdv']) || !isset($data['description'])) {
    error_log("❌ Champs manquants dans la requête");
    echo json_encode(['success' => false, 'message' => 'Champs manquants']);
    exit;
}

$id_rdv = intval($data['id_rdv']);
$description = trim($data['description']);

// Exécution de la requête avec PDO
try {
    $stmt = $pdo->prepare("UPDATE appointments SET description = ? WHERE id = ?");
    $stmt->execute([$description, $id_rdv]);

    error_log("✅ Description mise à jour avec succès");
    echo json_encode(['success' => true, 'message' => 'Description mise à jour']);
} catch (PDOException $e) {
    error_log("❌ Erreur PDO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour', 'error' => $e->getMessage()]);
}

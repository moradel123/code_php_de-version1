<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Check authentication
session_start();
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database configuration
$host = "localhost";
$dbname = "hospital";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get input data (supports both GET and JSON body)
    $inputData = json_decode(file_get_contents('php://input'), true) ?? $_GET;
    $id = $inputData['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Check if patient exists first
        $checkStmt = $conn->prepare("SELECT id FROM patients WHERE id = ?");
        $checkStmt->execute([$id]);
        
        if ($checkStmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
            exit;
        }

        // Delete related appointments
        $appointmentStmt = $conn->prepare("DELETE FROM rendezvous WHERE patient_id = ?");
        $appointmentStmt->execute([$id]);

        // Delete patient
        $patientStmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
        $patientStmt->execute([$id]);

        if ($patientStmt->rowCount() > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Patient deleted successfully',
                'deletedId' => $id
            ]);
        } else {
            $conn->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        throw $e;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
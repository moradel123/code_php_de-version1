<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

$inputData = json_decode(file_get_contents("php://input"), true);
error_log("Received input: " . json_encode($inputData));

if (isset($inputData['category']) && isset($inputData['id'])) {
    $category = $inputData['category'];
    $id = $inputData['id'];

    error_log("Category: " . $category);
    error_log("ID: " . $id);

    $validCategories = [
        'patients' => 'patients',
        'doctors' => 'doctors',
        'infermiers' => 'infermiers',
        'departements' => 'departements'
    ];

    if (!array_key_exists($category, $validCategories)) {
        error_log("Invalid category: " . $category);
        echo json_encode(["error" => "Invalid category"]);
        exit();
    }

    $tableName = $validCategories[$category];

    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "hospital_management";

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        echo json_encode(["error" => "Database connection failed"]);
        exit();
    }

    try {
        $checkSql = "SELECT id FROM $tableName WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt === false) {
            error_log("Error preparing the check SQL statement: " . $conn->error);
            echo json_encode(["error" => "Error preparing the check SQL statement"]);
            exit();
        }

        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            error_log("No record found with ID $id in table $tableName");
            echo json_encode(["error" => "No record found with this ID"]);
            $checkStmt->close();
            $conn->close();
            exit();
        }

        $checkStmt->close();

        $sql = "DELETE FROM $tableName WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            error_log("Error preparing the SQL statement: " . $conn->error);
            echo json_encode(["error" => "Error preparing the SQL statement"]);
            exit();
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            error_log("Record with ID $id deleted successfully");
            echo json_encode(["success" => "Record deleted successfully"]);
        } else {
            error_log("No record found with ID $id to delete");
            echo json_encode(["error" => "No record found with this ID"]);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["error" => "Error deleting record"]);
    }
} else {
    error_log("Missing parameters: category or id");
    echo json_encode(["error" => "Missing parameters"]);
}
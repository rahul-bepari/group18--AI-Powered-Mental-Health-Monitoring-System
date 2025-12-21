<?php
require_once 'db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $result = $conn->query("SELECT * FROM patient ORDER BY Patient_ID DESC");
            $patients = [];
            
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }
            
            echo json_encode([
                "status" => "success",
                "data" => $patients
            ]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid JSON data"
                ]);
                break;
            }
            
            // Validate required fields
            $required = ['Name', 'Age', 'Email'];
            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Missing required field: $field"
                    ]);
                    exit;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO patient (Name, Age, Gender, Contact, address, Email, emergencyContact) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sisssss",
                $input['Name'],
                $input['Age'],
                $input['Gender'] ?? '',
                $input['Contact'] ?? '',
                $input['address'] ?? '',
                $input['Email'],
                $input['emergencyContact'] ?? ''
            );
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Patient created successfully",
                    "id" => $stmt->insert_id
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to create patient: " . $stmt->error
                ]);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['Patient_ID'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid data or missing Patient_ID"
                ]);
                break;
            }
            
            $stmt = $conn->prepare("UPDATE patient SET Name=?, Age=?, Gender=?, Contact=?, address=?, Email=?, emergencyContact=? WHERE Patient_ID=?");
            $stmt->bind_param(
                "sisssssi",
                $input['Name'],
                $input['Age'],
                $input['Gender'],
                $input['Contact'],
                $input['address'],
                $input['Email'],
                $input['emergencyContact'],
                $input['Patient_ID']
            );
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Patient updated successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update patient: " . $stmt->error
                ]);
            }
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['Patient_ID'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing Patient_ID"
                ]);
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM patient WHERE Patient_ID = ?");
            $stmt->bind_param("i", $input['Patient_ID']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Patient deleted successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to delete patient: " . $stmt->error
                ]);
            }
            break;
            
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Method not allowed"
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}

$conn->close();
?>
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
            $patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
            
            if ($patient_id > 0) {
                $stmt = $conn->prepare("SELECT * FROM dailylogs WHERE Patient_ID = ? ORDER BY Timestamp DESC");
                $stmt->bind_param("i", $patient_id);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query("SELECT dl.*, p.Name as PatientName FROM dailylogs dl LEFT JOIN patient p ON dl.Patient_ID = p.Patient_ID ORDER BY dl.Timestamp DESC");
            }
            
            $logs = [];
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
            
            echo json_encode([
                "status" => "success",
                "data" => $logs
            ]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['Patient_ID'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid data or missing Patient_ID"
                ]);
                break;
            }
            
            $stmt = $conn->prepare("INSERT INTO dailylogs (Patient_ID, Timestamp, Analysis_ID, Mood, StressLevel, SleepHours, Notes) VALUES (?, NOW(), 1, ?, ?, ?, ?)");
            $stmt->bind_param(
                "isiis",
                $input['Patient_ID'],
                $input['Mood'],
                $input['StressLevel'],
                $input['SleepHours'],
                $input['Notes'] ?? ''
            );
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Daily log created successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to create log: " . $stmt->error
                ]);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['Patient_ID']) || !isset($input['Timestamp'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing required fields"
                ]);
                break;
            }
            
            $stmt = $conn->prepare("UPDATE dailylogs SET Mood=?, StressLevel=?, SleepHours=?, Notes=? WHERE Patient_ID=? AND Timestamp=?");
            $stmt->bind_param(
                "siisss",
                $input['Mood'],
                $input['StressLevel'],
                $input['SleepHours'],
                $input['Notes'],
                $input['Patient_ID'],
                $input['Timestamp']
            );
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Daily log updated successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update log"
                ]);
            }
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['Patient_ID']) || !isset($input['Timestamp'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing required fields"
                ]);
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM dailylogs WHERE Patient_ID=? AND Timestamp=?");
            $stmt->bind_param("is", $input['Patient_ID'], $input['Timestamp']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Daily log deleted successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to delete log"
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
        "message" => "Server error"
    ]);
}

$conn->close();
?>
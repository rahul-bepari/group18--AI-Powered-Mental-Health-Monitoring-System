<?php
// template_crud.php - Use this as template for all CRUD operations
require_once 'db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$table_name = "YOUR_TABLE_NAME"; // CHANGE THIS
$primary_key = "YOUR_PRIMARY_KEY"; // CHANGE THIS

try {
    switch ($method) {
        case 'GET':
            // Get single record or all records
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM $table_name WHERE $primary_key = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                
                echo json_encode([
                    "status" => "success",
                    "data" => $data
                ]);
            } else {
                $result = $conn->query("SELECT * FROM $table_name ORDER BY $primary_key DESC");
                $data = [];
                
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                
                echo json_encode([
                    "status" => "success",
                    "data" => $data
                ]);
            }
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
            
            // Build INSERT query dynamically
            $columns = [];
            $placeholders = [];
            $values = [];
            $types = "";
            
            foreach ($input as $key => $value) {
                $columns[] = $key;
                $placeholders[] = "?";
                $values[] = $value;
                $types .= get_param_type($value);
            }
            
            $sql = "INSERT INTO $table_name (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Record created successfully",
                    "id" => $stmt->insert_id
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to create record: " . $stmt->error
                ]);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input[$primary_key])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid data or missing primary key"
                ]);
                break;
            }
            
            // Build UPDATE query dynamically
            $updates = [];
            $values = [];
            $types = "";
            $id = $input[$primary_key];
            
            foreach ($input as $key => $value) {
                if ($key !== $primary_key) {
                    $updates[] = "$key = ?";
                    $values[] = $value;
                    $types .= get_param_type($value);
                }
            }
            
            $values[] = $id;
            $types .= get_param_type($id);
            
            $sql = "UPDATE $table_name SET " . implode(", ", $updates) . " WHERE $primary_key = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Record updated successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update record: " . $stmt->error
                ]);
            }
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input[$primary_key])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Missing primary key"
                ]);
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM $table_name WHERE $primary_key = ?");
            $stmt->bind_param("i", $input[$primary_key]);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Record deleted successfully"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to delete record: " . $stmt->error
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

function get_param_type($value) {
    if (is_int($value)) return 'i';
    if (is_double($value)) return 'd';
    return 's';
}

$conn->close();
?>
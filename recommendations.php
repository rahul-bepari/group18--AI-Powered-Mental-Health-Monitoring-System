<?php
include 'db.php';

header('Content-Type: application/json');

class Recommendations {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($patientId, $tipCategory, $content) {
        $timestamp = date('Y-m-d H:i:s');
        $analysisId = 1; // Default or generate based on your logic
        
        $stmt = $this->conn->prepare("INSERT INTO recommendations (Patient_ID, Timestamp, Analysis_ID, TipCategory, Content) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $patientId, $timestamp, $analysisId, $tipCategory, $content);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Recommendation created successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get recommendations by patient)
    public function readByPatient($patientId) {
        $stmt = $this->conn->prepare("SELECT * FROM recommendations WHERE Patient_ID = ? ORDER BY Timestamp DESC");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
        return json_encode(["status" => "success", "data" => $recommendations]);
    }
    
    // READ (Get recommendations by category)
    public function readByCategory($category) {
        $stmt = $this->conn->prepare("SELECT * FROM recommendations WHERE TipCategory = ? ORDER BY Timestamp DESC");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
        return json_encode(["status" => "success", "data" => $recommendations]);
    }
    
    // GET categories summary
    public function getCategories() {
        $result = $this->conn->query("SELECT DISTINCT TipCategory, COUNT(*) as count FROM recommendations GROUP BY TipCategory ORDER BY count DESC");
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return json_encode(["status" => "success", "data" => $categories]);
    }
    
    // DELETE
    public function delete($patientId, $timestamp) {
        $stmt = $this->conn->prepare("DELETE FROM recommendations WHERE Patient_ID = ? AND Timestamp = ?");
        $stmt->bind_param("is", $patientId, $timestamp);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Recommendation deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$recommendation = new Recommendations($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['patient_id'])) {
            echo $recommendation->readByPatient($_GET['patient_id']);
        } elseif (isset($_GET['category'])) {
            echo $recommendation->readByCategory($_GET['category']);
        } elseif (isset($_GET['categories']) && $_GET['categories'] == 'true') {
            echo $recommendation->getCategories();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $recommendation->create(
            $data['Patient_ID'],
            $data['TipCategory'],
            $data['Content']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $recommendation->delete($data['Patient_ID'], $data['Timestamp']);
        break;
}

$conn->close();
?>
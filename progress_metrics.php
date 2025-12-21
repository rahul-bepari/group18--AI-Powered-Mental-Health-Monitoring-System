<?php
include 'db.php';

header('Content-Type: application/json');

class ProgressMetrics {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($sessionsId, $metricName, $emotionalStabilityScore, $anxietyLevelScore, $fatigueScore) {
        $stmt = $this->conn->prepare("INSERT INTO progess_metrics (Sessions_ID, metricName, emtional_StabilityScore, anxityLevelScore, fatigueScore) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiii", $sessionsId, $metricName, $emotionalStabilityScore, $anxietyLevelScore, $fatigueScore);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Progress metrics created successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get metrics by session)
    public function readBySession($sessionsId) {
        $stmt = $this->conn->prepare("SELECT * FROM progess_metrics WHERE Sessions_ID = ?");
        $stmt->bind_param("i", $sessionsId);
        $stmt->execute();
        $result = $stmt->get_result();
        $metrics = $result->fetch_assoc();
        
        if ($metrics) {
            return json_encode(["status" => "success", "data" => $metrics]);
        } else {
            return json_encode(["status" => "error", "message" => "Metrics not found"]);
        }
    }
    
    // GET progress trend for patient
    public function getProgressTrend($patientId) {
        $stmt = $this->conn->prepare("
            SELECT pm.*, s.day 
            FROM progess_metrics pm 
            JOIN sessions s ON pm.Sessions_ID = s.Sessions_ID 
            WHERE s.Patient_ID = ? 
            ORDER BY s.day ASC
        ");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }
        return json_encode(["status" => "success", "data" => $trends]);
    }
    
    // UPDATE
    public function update($sessionsId, $metricName, $emotionalStabilityScore, $anxietyLevelScore, $fatigueScore) {
        $stmt = $this->conn->prepare("UPDATE progess_metrics SET metricName=?, emtional_StabilityScore=?, anxityLevelScore=?, fatigueScore=? WHERE Sessions_ID=?");
        $stmt->bind_param("siiii", $metricName, $emotionalStabilityScore, $anxietyLevelScore, $fatigueScore, $sessionsId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Progress metrics updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($sessionsId) {
        $stmt = $this->conn->prepare("DELETE FROM progess_metrics WHERE Sessions_ID = ?");
        $stmt->bind_param("i", $sessionsId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Progress metrics deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$progress = new ProgressMetrics($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['session_id'])) {
            echo $progress->readBySession($_GET['session_id']);
        } elseif (isset($_GET['patient_id'])) {
            echo $progress->getProgressTrend($_GET['patient_id']);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $progress->create(
            $data['Sessions_ID'],
            $data['metricName'],
            $data['emtional_StabilityScore'],
            $data['anxityLevelScore'],
            $data['fatigueScore']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $progress->update(
            $data['Sessions_ID'],
            $data['metricName'],
            $data['emtional_StabilityScore'],
            $data['anxityLevelScore'],
            $data['fatigueScore']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $progress->delete($data['Sessions_ID']);
        break;
}

$conn->close();
?>
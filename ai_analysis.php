<?php
include 'db.php';

header('Content-Type: application/json');

class AIAnalysis {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($sentimentScore, $riskScore, $emotionClassification) {
        $stmt = $this->conn->prepare("INSERT INTO aianalysis (SentimentScore, RiskScore, EmotionClassification) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sentimentScore, $riskScore, $emotionClassification);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "AI analysis created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all analyses)
    public function readAll() {
        $result = $this->conn->query("SELECT * FROM aianalysis ORDER BY Analysis_ID DESC");
        $analyses = [];
        while ($row = $result->fetch_assoc()) {
            $analyses[] = $row;
        }
        return json_encode(["status" => "success", "data" => $analyses]);
    }
    
    // GET analysis summary
    public function getSummary() {
        $result = $this->conn->query("
            SELECT 
                AVG(SentimentScore) as avg_sentiment,
                AVG(RiskScore) as avg_risk,
                COUNT(*) as total_analyses
            FROM aianalysis
        ");
        $summary = $result->fetch_assoc();
        
        return json_encode(["status" => "success", "data" => $summary]);
    }
    
    // UPDATE
    public function update($analysisId, $sentimentScore, $riskScore, $emotionClassification) {
        $stmt = $this->conn->prepare("UPDATE aianalysis SET SentimentScore=?, RiskScore=?, EmotionClassification=? WHERE Analysis_ID=?");
        $stmt->bind_param("iisi", $sentimentScore, $riskScore, $emotionClassification, $analysisId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "AI analysis updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($analysisId) {
        $stmt = $this->conn->prepare("DELETE FROM aianalysis WHERE Analysis_ID = ?");
        $stmt->bind_param("i", $analysisId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "AI analysis deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$aiAnalysis = new AIAnalysis($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['summary']) && $_GET['summary'] == 'true') {
            echo $aiAnalysis->getSummary();
        } else {
            echo $aiAnalysis->readAll();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $aiAnalysis->create(
            $data['SentimentScore'],
            $data['RiskScore'],
            $data['EmotionClassification']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $aiAnalysis->update(
            $data['Analysis_ID'],
            $data['SentimentScore'],
            $data['RiskScore'],
            $data['EmotionClassification']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $aiAnalysis->delete($data['Analysis_ID']);
        break;
}

$conn->close();
?>
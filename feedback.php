<?php
include 'db.php';

header('Content-Type: application/json');

class Feedback {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($patientId, $sessionsId, $rating, $comments) {
        $submittedDate = date('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("INSERT INTO feedback (Patient_ID, Sessions_ID, Rating, Comments, SubmittedDate) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $patientId, $sessionsId, $rating, $comments, $submittedDate);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Feedback submitted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all feedback)
    public function readAll() {
        $result = $this->conn->query("
            SELECT f.*, p.Name as PatientName, s.day as SessionDate 
            FROM feedback f 
            LEFT JOIN patient p ON f.Patient_ID = p.Patient_ID 
            LEFT JOIN sessions s ON f.Sessions_ID = s.Sessions_ID 
            ORDER BY f.SubmittedDate DESC
        ");
        $feedbacks = [];
        while ($row = $result->fetch_assoc()) {
            $feedbacks[] = $row;
        }
        return json_encode(["status" => "success", "data" => $feedbacks]);
    }
    
    // READ (Get feedback by session)
    public function readBySession($sessionId) {
        $stmt = $this->conn->prepare("SELECT * FROM feedback WHERE Sessions_ID = ? ORDER BY SubmittedDate DESC");
        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $feedbacks = [];
        while ($row = $result->fetch_assoc()) {
            $feedbacks[] = $row;
        }
        return json_encode(["status" => "success", "data" => $feedbacks]);
    }
    
    // GET average rating
    public function getAverageRating($counsellorId = null) {
        if ($counsellorId) {
            $stmt = $this->conn->prepare("
                SELECT AVG(f.Rating) as avg_rating 
                FROM feedback f 
                JOIN sessions s ON f.Sessions_ID = s.Sessions_ID 
                WHERE s.Counsellor_ID = ?
            ");
            $stmt->bind_param("i", $counsellorId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return json_encode(["status" => "success", "average_rating" => round($row['avg_rating'], 2)]);
        } else {
            $result = $this->conn->query("SELECT AVG(Rating) as avg_rating FROM feedback");
            $row = $result->fetch_assoc();
            
            return json_encode(["status" => "success", "average_rating" => round($row['avg_rating'], 2)]);
        }
    }
    
    // DELETE
    public function delete($feedbackId) {
        $stmt = $this->conn->prepare("DELETE FROM feedback WHERE feedback_ID = ?");
        $stmt->bind_param("i", $feedbackId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Feedback deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$feedback = new Feedback($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['session_id'])) {
            echo $feedback->readBySession($_GET['session_id']);
        } elseif (isset($_GET['avg_rating'])) {
            echo $feedback->getAverageRating(isset($_GET['counsellor_id']) ? $_GET['counsellor_id'] : null);
        } else {
            echo $feedback->readAll();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $feedback->create(
            $data['Patient_ID'],
            $data['Sessions_ID'],
            $data['Rating'],
            $data['Comments']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $feedback->delete($data['feedback_ID']);
        break;
}

$conn->close();
?>
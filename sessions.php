<?php
include 'db.php';

header('Content-Type: application/json');

class Sessions {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($patientId, $counsellorId, $paymentId, $day, $startTime, $endTime, $notes) {
        $stmt = $this->conn->prepare("INSERT INTO sessions (Patient_ID, Counsellor_ID, Payment_ID, day, StartTime, EndTime, Notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissss", $patientId, $counsellorId, $paymentId, $day, $startTime, $endTime, $notes);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Session created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all sessions)
    public function readAll() {
        $result = $this->conn->query("
            SELECT s.*, p.Name as PatientName, c.Name as CounsellorName 
            FROM sessions s 
            LEFT JOIN patient p ON s.Patient_ID = p.Patient_ID 
            LEFT JOIN counsellors c ON s.Counsellor_ID = c.Counsellor_ID 
            ORDER BY s.day DESC, s.StartTime DESC
        ");
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
        return json_encode(["status" => "success", "data" => $sessions]);
    }
    
    // READ (Get sessions by patient)
    public function readByPatient($patientId) {
        $stmt = $this->conn->prepare("
            SELECT s.*, c.Name as CounsellorName 
            FROM sessions s 
            LEFT JOIN counsellors c ON s.Counsellor_ID = c.Counsellor_ID 
            WHERE s.Patient_ID = ? 
            ORDER BY s.day DESC, s.StartTime DESC
        ");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
        return json_encode(["status" => "success", "data" => $sessions]);
    }
    
    // UPDATE
    public function update($sessionId, $patientId, $counsellorId, $paymentId, $day, $startTime, $endTime, $notes) {
        $stmt = $this->conn->prepare("UPDATE sessions SET Patient_ID=?, Counsellor_ID=?, Payment_ID=?, day=?, StartTime=?, EndTime=?, Notes=? WHERE Sessions_ID=?");
        $stmt->bind_param("iiissssi", $patientId, $counsellorId, $paymentId, $day, $startTime, $endTime, $notes, $sessionId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Session updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($sessionId) {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE Sessions_ID = ?");
        $stmt->bind_param("i", $sessionId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Session deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$session = new Sessions($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['patient_id'])) {
            echo $session->readByPatient($_GET['patient_id']);
        } else {
            echo $session->readAll();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $session->create(
            $data['Patient_ID'],
            $data['Counsellor_ID'],
            $data['Payment_ID'],
            $data['day'],
            $data['StartTime'],
            $data['EndTime'],
            $data['Notes']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $session->update(
            $data['Sessions_ID'],
            $data['Patient_ID'],
            $data['Counsellor_ID'],
            $data['Payment_ID'],
            $data['day'],
            $data['StartTime'],
            $data['EndTime'],
            $data['Notes']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $session->delete($data['Sessions_ID']);
        break;
}

$conn->close();
?>
<?php
include 'db.php';

header('Content-Type: application/json');

class CrisisAlerts {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($patientId, $teamId, $caregiverId, $triggers) {
        $alertTimestamp = date('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("INSERT INTO crisisalerts (Patient_ID, TeamID, Caregiver_ID, AlertTimestamp, Triggers) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $patientId, $teamId, $caregiverId, $alertTimestamp, $triggers);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Crisis alert created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all alerts)
    public function readAll() {
        $result = $this->conn->query("
            SELECT ca.*, p.Name as PatientName, t.Team_Name, cg.Name as CaregiverName 
            FROM crisisalerts ca 
            LEFT JOIN patient p ON ca.Patient_ID = p.Patient_ID 
            LEFT JOIN emergencysosteam t ON ca.TeamID = t.TeamID 
            LEFT JOIN caregiver cg ON ca.Caregiver_ID = cg.Caregiver_ID 
            ORDER BY ca.AlertTimestamp DESC
        ");
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        return json_encode(["status" => "success", "data" => $alerts]);
    }
    
    // READ (Get recent alerts - last 24 hours)
    public function readRecent() {
        $date = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $stmt = $this->conn->prepare("SELECT * FROM crisisalerts WHERE AlertTimestamp > ? ORDER BY AlertTimestamp DESC");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        return json_encode(["status" => "success", "data" => $alerts]);
    }
    
    // DELETE
    public function delete($alertId) {
        $stmt = $this->conn->prepare("DELETE FROM crisisalerts WHERE Alert_ID = ?");
        $stmt->bind_param("i", $alertId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Crisis alert deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$crisisAlert = new CrisisAlerts($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['recent']) && $_GET['recent'] == 'true') {
            echo $crisisAlert->readRecent();
        } else {
            echo $crisisAlert->readAll();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $crisisAlert->create(
            $data['Patient_ID'],
            $data['TeamID'],
            $data['Caregiver_ID'],
            $data['Triggers']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $crisisAlert->delete($data['Alert_ID']);
        break;
}

$conn->close();
?>
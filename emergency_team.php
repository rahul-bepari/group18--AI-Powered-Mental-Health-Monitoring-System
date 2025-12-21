<?php
include 'db.php';

header('Content-Type: application/json');

class EmergencyTeam {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($teamName, $contact, $availability, $responseProtocol) {
        $stmt = $this->conn->prepare("INSERT INTO emergencysosteam (Team_Name, Contact, Availability, ResponseProtocol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $teamName, $contact, $availability, $responseProtocol);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Emergency team created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all teams)
    public function readAll() {
        $result = $this->conn->query("SELECT * FROM emergencysosteam");
        $teams = [];
        while ($row = $result->fetch_assoc()) {
            $teams[] = $row;
        }
        return json_encode(["status" => "success", "data" => $teams]);
    }
    
    // UPDATE
    public function update($teamId, $teamName, $contact, $availability, $responseProtocol) {
        $stmt = $this->conn->prepare("UPDATE emergencysosteam SET Team_Name=?, Contact=?, Availability=?, ResponseProtocol=? WHERE TeamID=?");
        $stmt->bind_param("ssssi", $teamName, $contact, $availability, $responseProtocol, $teamId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Emergency team updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($teamId) {
        $stmt = $this->conn->prepare("DELETE FROM emergencysosteam WHERE TeamID = ?");
        $stmt->bind_param("i", $teamId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Emergency team deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$emergencyTeam = new EmergencyTeam($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo $emergencyTeam->readAll();
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $emergencyTeam->create(
            $data['Team_Name'],
            $data['Contact'],
            $data['Availability'],
            $data['ResponseProtocol']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $emergencyTeam->update(
            $data['TeamID'],
            $data['Team_Name'],
            $data['Contact'],
            $data['Availability'],
            $data['ResponseProtocol']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $emergencyTeam->delete($data['TeamID']);
        break;
}

$conn->close();
?>
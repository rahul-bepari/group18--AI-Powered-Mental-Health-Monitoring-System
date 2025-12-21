<?php
include 'db.php';

header('Content-Type: application/json');

class Counsellors {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($name, $specialization, $contact, $availableDay, $startTime, $endTime) {
        $stmt = $this->conn->prepare("INSERT INTO counsellors (Name, Specialization, Contact, AvailableDay, StartTime, EndTime) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $specialization, $contact, $availableDay, $startTime, $endTime);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Counsellor created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all counsellors)
    public function readAll() {
        $result = $this->conn->query("SELECT * FROM counsellors");
        $counsellors = [];
        while ($row = $result->fetch_assoc()) {
            $counsellors[] = $row;
        }
        return json_encode(["status" => "success", "data" => $counsellors]);
    }
    
    // UPDATE
    public function update($id, $name, $specialization, $contact, $availableDay, $startTime, $endTime) {
        $stmt = $this->conn->prepare("UPDATE counsellors SET Name=?, Specialization=?, Contact=?, AvailableDay=?, StartTime=?, EndTime=? WHERE Counsellor_ID=?");
        $stmt->bind_param("ssssssi", $name, $specialization, $contact, $availableDay, $startTime, $endTime, $id);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Counsellor updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM counsellors WHERE Counsellor_ID = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Counsellor deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$counsellor = new Counsellors($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        echo $counsellor->readAll();
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $counsellor->create(
            $data['Name'],
            $data['Specialization'],
            $data['Contact'],
            $data['AvailableDay'],
            $data['StartTime'],
            $data['EndTime']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $counsellor->update(
            $data['Counsellor_ID'],
            $data['Name'],
            $data['Specialization'],
            $data['Contact'],
            $data['AvailableDay'],
            $data['StartTime'],
            $data['EndTime']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $counsellor->delete($data['Counsellor_ID']);
        break;
}

$conn->close();
?>
<?php
include 'db.php';

header('Content-Type: application/json');

class Caregiver {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($patientId, $name, $email, $contact, $accessPermissions) {
        $stmt = $this->conn->prepare("INSERT INTO caregiver (Patient_ID, Name, Email, Contact, AccessPermissions) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $patientId, $name, $email, $contact, $accessPermissions);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Caregiver created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all caregivers)
    public function readAll() {
        $result = $this->conn->query("SELECT cg.*, p.Name as PatientName FROM caregiver cg LEFT JOIN patient p ON cg.Patient_ID = p.Patient_ID");
        $caregivers = [];
        while ($row = $result->fetch_assoc()) {
            $caregivers[] = $row;
        }
        return json_encode(["status" => "success", "data" => $caregivers]);
    }
    
    // READ (Get caregivers by patient)
    public function readByPatient($patientId) {
        $stmt = $this->conn->prepare("SELECT * FROM caregiver WHERE Patient_ID = ?");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $caregivers = [];
        while ($row = $result->fetch_assoc()) {
            $caregivers[] = $row;
        }
        return json_encode(["status" => "success", "data" => $caregivers]);
    }
    
    // UPDATE
    public function update($caregiverId, $name, $email, $contact, $accessPermissions) {
        $stmt = $this->conn->prepare("UPDATE caregiver SET Name=?, Email=?, Contact=?, AccessPermissions=? WHERE Caregiver_ID=?");
        $stmt->bind_param("ssssi", $name, $email, $contact, $accessPermissions, $caregiverId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Caregiver updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($caregiverId) {
        $stmt = $this->conn->prepare("DELETE FROM caregiver WHERE Caregiver_ID = ?");
        $stmt->bind_param("i", $caregiverId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Caregiver deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$caregiver = new Caregiver($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['patient_id'])) {
            echo $caregiver->readByPatient($_GET['patient_id']);
        } else {
            echo $caregiver->readAll();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $caregiver->create(
            $data['Patient_ID'],
            $data['Name'],
            $data['Email'],
            $data['Contact'],
            $data['AccessPermissions']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $caregiver->update(
            $data['Caregiver_ID'],
            $data['Name'],
            $data['Email'],
            $data['Contact'],
            $data['AccessPermissions']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $caregiver->delete($data['Caregiver_ID']);
        break;
}

$conn->close();
?>
<?php
include 'db.php';

header('Content-Type: application/json');

class PatientPreference {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE or UPDATE
    public function save($patientId, $online, $offline, $journal) {
        // Check if preference exists
        $checkStmt = $this->conn->prepare("SELECT * FROM patient_preference WHERE Patient_ID = ?");
        $checkStmt->bind_param("i", $patientId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing
            $stmt = $this->conn->prepare("UPDATE patient_preference SET online=?, offline=?, journal=? WHERE Patient_ID=?");
            $stmt->bind_param("sssi", $online, $offline, $journal, $patientId);
            $message = "Preferences updated successfully";
        } else {
            // Create new
            $stmt = $this->conn->prepare("INSERT INTO patient_preference (Patient_ID, online, offline, journal) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $patientId, $online, $offline, $journal);
            $message = "Preferences created successfully";
        }
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => $message]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get preference by patient)
    public function readByPatient($patientId) {
        $stmt = $this->conn->prepare("SELECT * FROM patient_preference WHERE Patient_ID = ?");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        $preference = $result->fetch_assoc();
        
        if ($preference) {
            return json_encode(["status" => "success", "data" => $preference]);
        } else {
            return json_encode(["status" => "error", "message" => "Preferences not found"]);
        }
    }
    
    // DELETE
    public function delete($patientId) {
        $stmt = $this->conn->prepare("DELETE FROM patient_preference WHERE Patient_ID = ?");
        $stmt->bind_param("i", $patientId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Preferences deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$preference = new PatientPreference($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['patient_id'])) {
            echo $preference->readByPatient($_GET['patient_id']);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $preference->save(
            $data['Patient_ID'],
            $data['online'],
            $data['offline'],
            $data['journal']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $preference->delete($data['Patient_ID']);
        break;
}

$conn->close();
?>
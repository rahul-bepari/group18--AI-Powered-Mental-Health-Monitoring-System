<?php
include 'db.php';

header('Content-Type: application/json');

class Payment {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // CREATE
    public function create($patientId, $amount, $paymentMethod, $status) {
        $paymentDate = date('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("INSERT INTO payment (Patient_ID, Amount, PaymentMethod, PaymentDate, Status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $patientId, $amount, $paymentMethod, $paymentDate, $status);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Payment created successfully", "id" => $stmt->insert_id]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // READ (Get all payments)
    public function readAll() {
        $result = $this->conn->query("SELECT p.*, pt.Name as PatientName FROM payment p LEFT JOIN patient pt ON p.Patient_ID = pt.Patient_ID ORDER BY p.PaymentDate DESC");
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return json_encode(["status" => "success", "data" => $payments]);
    }
    
    // READ (Get payments by patient)
    public function readByPatient($patientId) {
        $stmt = $this->conn->prepare("SELECT * FROM payment WHERE Patient_ID = ? ORDER BY PaymentDate DESC");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return json_encode(["status" => "success", "data" => $payments]);
    }
    
    // UPDATE (Update payment status)
    public function updateStatus($paymentId, $status) {
        $stmt = $this->conn->prepare("UPDATE payment SET Status = ? WHERE Payment_ID = ?");
        $stmt->bind_param("si", $status, $paymentId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Payment status updated successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
    
    // DELETE
    public function delete($paymentId) {
        $stmt = $this->conn->prepare("DELETE FROM payment WHERE Payment_ID = ?");
        $stmt->bind_param("i", $paymentId);
        
        if ($stmt->execute()) {
            return json_encode(["status" => "success", "message" => "Payment deleted successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $stmt->error]);
        }
    }
}

// Handle requests
$payment = new Payment($conn);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['patient_id'])) {
            echo $payment->readByPatient($_GET['patient_id']);
        } else {
            echo $payment->readAll();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $payment->create(
            $data['Patient_ID'],
            $data['Amount'],
            $data['PaymentMethod'],
            $data['Status']
        );
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $payment->updateStatus(
            $data['Payment_ID'],
            $data['Status']
        );
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $payment->delete($data['Payment_ID']);
        break;
}

$conn->close();
?>
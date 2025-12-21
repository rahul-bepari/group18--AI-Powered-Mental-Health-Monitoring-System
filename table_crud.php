<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ai_mental_health';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'get_columns':
        getTableColumns();
        break;
    case 'get_auto_increment':
        getAutoIncrementColumns();
        break;
    case 'read':
        readTable();
        break;
    case 'search':
        searchTable();
        break;
    case 'get_record':
        getRecord();
        break;
    case 'add':
    case 'edit':
        saveRecord();
        break;
    case 'delete':
        deleteRecord();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getTableColumns() {
    global $conn;
    $table = $_GET['table'] ?? '';
    
    if (empty($table)) {
        echo json_encode(['success' => false, 'message' => 'Table name required']);
        return;
    }
    
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    echo json_encode(['success' => true, 'columns' => $columns]);
}

function getAutoIncrementColumns() {
    global $conn;
    $table = $_GET['table'] ?? '';
    
    if (empty($table)) {
        echo json_encode(['success' => false, 'message' => 'Table name required']);
        return;
    }
    
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $autoIncrementColumns = [];
    
    while($row = $result->fetch_assoc()) {
        if (strpos($row['Extra'], 'auto_increment') !== false) {
            $autoIncrementColumns[] = $row['Field'];
        }
    }
    
    echo json_encode(['success' => true, 'auto_increment_columns' => $autoIncrementColumns]);
}

function readTable() {
    global $conn;
    $table = $_GET['table'] ?? '';
    
    if (empty($table)) {
        echo json_encode(['success' => false, 'message' => 'Table name required']);
        return;
    }
    
    // Get columns
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Get data
    $data = [];
    $query = "SELECT * FROM `$table` ORDER BY " . $columns[0] . " DESC";
    $result = $conn->query($query);
    
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'columns' => $columns, 'data' => $data]);
}

function searchTable() {
    global $conn;
    $table = $_GET['table'] ?? '';
    $search = $_GET['search'] ?? '';
    
    if (empty($table)) {
        echo json_encode(['success' => false, 'message' => 'Table name required']);
        return;
    }
    
    // Get columns
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Build search query
    $conditions = [];
    foreach($columns as $column) {
        $conditions[] = "`$column` LIKE '%$search%'";
    }
    
    $whereClause = implode(' OR ', $conditions);
    $query = "SELECT * FROM `$table` WHERE $whereClause ORDER BY " . $columns[0] . " DESC";
    
    $data = [];
    $result = $conn->query($query);
    
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'columns' => $columns, 'data' => $data]);
}

function getRecord() {
    global $conn;
    $table = $_GET['table'] ?? '';
    $id = $_GET['id'] ?? '';
    
    if (empty($table) || empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Table name and ID required']);
        return;
    }
    
    // Get columns
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Get first column as ID column
    $idColumn = $columns[0];
    
    $query = "SELECT * FROM `$table` WHERE `$idColumn` = '$id' LIMIT 1";
    $result = $conn->query($query);
    
    $data = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $data]);
}

function saveRecord() {
    global $conn;
    $action = $_POST['action'];
    $table = $_POST['table'];
    $id = $_POST['id'] ?? '';
    
    // Get columns and auto-increment info
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    $autoIncrementColumns = [];
    
    while($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        if (strpos($row['Extra'], 'auto_increment') !== false) {
            $autoIncrementColumns[] = $row['Field'];
        }
    }
    
    // Prepare values array
    $values = [];
    foreach($columns as $column) {
        // Skip auto-increment columns for INSERT
        if ($action === 'add' && in_array($column, $autoIncrementColumns)) {
            continue;
        }
        
        if (isset($_POST[$column]) && $_POST[$column] !== '') {
            $values[$column] = $conn->real_escape_string($_POST[$column]);
        }
    }
    
    // Handle special cases for tables with required foreign keys
    if ($table === 'dailylogs') {
        // For dailylogs table, Analysis_ID is required
        if (!isset($values['Analysis_ID']) || empty($values['Analysis_ID'])) {
            // Check if an Analysis_ID exists, otherwise create one
            $checkResult = $conn->query("SELECT Analysis_ID FROM aianalysis LIMIT 1");
            if ($checkResult && $checkResult->num_rows > 0) {
                $row = $checkResult->fetch_assoc();
                $values['Analysis_ID'] = $row['Analysis_ID'];
            } else {
                // Create a default Analysis_ID
                $conn->query("INSERT INTO aianalysis (Patient_ID, SentimentScore, RiskScore, EmotionClassification) VALUES ('1', '50', '50', 'Neutral')");
                $values['Analysis_ID'] = $conn->insert_id;
            }
        }
    }
    
    if ($table === 'recommendations') {
        // For recommendations table, Analysis_ID might be required
        if (!isset($values['Analysis_ID']) || empty($values['Analysis_ID'])) {
            // Check if an Analysis_ID exists, otherwise create one
            $checkResult = $conn->query("SELECT Analysis_ID FROM aianalysis LIMIT 1");
            if ($checkResult && $checkResult->num_rows > 0) {
                $row = $checkResult->fetch_assoc();
                $values['Analysis_ID'] = $row['Analysis_ID'];
            } else {
                // Create a default Analysis_ID
                $conn->query("INSERT INTO aianalysis (Patient_ID, SentimentScore, RiskScore, EmotionClassification) VALUES ('1', '50', '50', 'Neutral')");
                $values['Analysis_ID'] = $conn->insert_id;
            }
        }
    }
    
    // Add timestamp for certain tables if not provided
    $timestampColumns = ['Timestamp', 'SubmittedDate', 'AlertTimestamp', 'PaymentDate'];
    foreach($timestampColumns as $timestampCol) {
        if (in_array($timestampCol, $columns) && (!isset($values[$timestampCol]) || empty($values[$timestampCol]))) {
            $values[$timestampCol] = date('Y-m-d H:i:s');
        }
    }
    
    if ($action === 'add') {
        $columnsStr = implode(', ', array_keys($values));
        $valuesStr = "'" . implode("', '", array_values($values)) . "'";
        
        $query = "INSERT INTO `$table` ($columnsStr) VALUES ($valuesStr)";
    } else { // edit
        $idColumn = $columns[0];
        $setParts = [];
        
        foreach($values as $column => $value) {
            $setParts[] = "`$column` = '$value'";
        }
        
        $setStr = implode(', ', $setParts);
        $query = "UPDATE `$table` SET $setStr WHERE `$idColumn` = '$id'";
    }
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $conn->error]);
    }
}

function deleteRecord() {
    global $conn;
    $table = $_POST['table'];
    $id = $_POST['id'];
    $idColumn = $_POST['id_column'] ?? '';
    
    if (empty($table) || empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Table name and ID required']);
        return;
    }
    
    // If id_column is not provided, get the first column
    if (empty($idColumn)) {
        $result = $conn->query("SHOW COLUMNS FROM `$table`");
        $columns = [];
        while($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $idColumn = $columns[0];
    }
    
    $query = "DELETE FROM `$table` WHERE `$idColumn` = '$id'";
    
    if ($conn->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $conn->error]);
    }
}

$conn->close();
?>
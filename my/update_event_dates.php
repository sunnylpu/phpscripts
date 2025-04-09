<?php
// Initialize database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "calendar_events";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Handle AJAX request to update event dates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id"])) {
    $event_id = intval($_POST["id"]);
    $start = $conn->real_escape_string($_POST["start"]);
    $end = isset($_POST["end"]) && !empty($_POST["end"]) ? $conn->real_escape_string($_POST["end"]) : null;
    
    // Update the event dates
    if ($end) {
        $sql = "UPDATE events SET start_date = '$start', end_date = '$end' WHERE id = $event_id";
    } else {
        $sql = "UPDATE events SET start_date = '$start' WHERE id = $event_id";
    }
    
    if ($conn->query($sql) !== TRUE) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $conn->error]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>
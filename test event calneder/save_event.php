<?php
// DB connection
$host = "localhost";
$username = "root"; // your MySQL username
$password = "";     // your MySQL password
$database = "event_calendar";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed"]));
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$title = $conn->real_escape_string($data['title']);
$start = $conn->real_escape_string($data['start']);
$end = $conn->real_escape_string($data['end']);
$color = $conn->real_escape_string($data['color']);
$description = $conn->real_escape_string($data['description']);

// Insert query
$query = "INSERT INTO events (title, start, end, color, description) 
          VALUES ('$title', '$start', '$end', '$color', '$description')";

if ($conn->query($query)) {
    echo json_encode(["status" => "success", "message" => "Event saved"]);
} else {
    echo json_encode(["status" => "error", "message" => "DB insert failed"]);
}

$conn->close();
?>

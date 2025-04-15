<?php
header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Validate required fields
if (empty($data['date']) || empty($data['title']) || empty($data['time'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Sanitize input
$event = [
    'id' => $data['id'] ?? uniqid(),
    'date' => filter_var($data['date'], FILTER_SANITIZE_STRING),
    'title' => filter_var($data['title'], FILTER_SANITIZE_STRING),
    'time' => filter_var($data['time'], FILTER_SANITIZE_STRING),
    'description' => filter_var($data['description'] ?? '', FILTER_SANITIZE_STRING)
];

// Read existing events
$eventsFile = 'events.json';
$events = [];

if (file_exists($eventsFile)) {
    $events = json_decode(file_get_contents($eventsFile), true) ?? [];
}

// Add or update event
$events[$event['date']][$event['id']] = $event;

// Save events
if (file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'event' => $event]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save event']);
} 
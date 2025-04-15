<?php
header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || empty($data['id']) || empty($data['date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

$eventsFile = 'events.json';
$events = [];

if (file_exists($eventsFile)) {
    $events = json_decode(file_get_contents($eventsFile), true) ?? [];
}

// Remove the event
if (isset($events[$data['date']][$data['id']])) {
    unset($events[$data['date']][$data['id']]);
    
    // Remove the date if it has no events
    if (empty($events[$data['date']])) {
        unset($events[$data['date']]);
    }
    
    // Save the updated events
    if (file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete event']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Event not found']);
} 
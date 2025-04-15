<?php
header('Content-Type: application/json');

$eventsFile = 'events.json';

if (file_exists($eventsFile)) {
    $events = json_decode(file_get_contents($eventsFile), true) ?? [];
    echo json_encode($events);
} else {
    echo json_encode([]);
} 
<?php
// Quick database seeding script
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/db.php';
    // Use centralized DB connection (configured in db.php)
    $db = get_db();
    
    // Check if events already exist
    $result = $db->query("SELECT COUNT(*) as count FROM events");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Database already has {$row['count']} events",
            'action' => 'none'
        ]);
        exit;
    }
    
    // Insert sample events
    $events = [
        ['Legazpi City Festival 2026', 'Legazpi City, Albay', 10000, '2026-03-15 14:00:00', 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30'],
        ['Summer Community Gathering', 'Legazpi City Downtown', 5000, '2026-04-20 10:00:00', 'https://images.unsplash.com/photo-1519671482677-504be0ffbc87'],
        ['Albay Grand Fiesta', 'Legazpi City Sports Complex', 15000, '2026-05-01 06:00:00', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87']
    ];
    
    $inserted = 0;
    foreach ($events as $event) {
        $stmt = $db->prepare("INSERT INTO events (title, location, capacity, datetime, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiss', $event[0], $event[1], $event[2], $event[3], $event[4]);
        if ($stmt->execute()) {
            $inserted++;
            $eventId = $stmt->insert_id;
            
            // Insert ML predictions
            $attendance = (int)($event[2] * 0.7);
            $waste = (int)($event[2] * 0.6);
            $crowding = 0.35;
            
            $predStmt = $db->prepare("INSERT INTO ml_predictions (event_id, attendance, waste_prediction, overcrowding_probability) VALUES (?, ?, ?, ?)");
            $predStmt->bind_param('idid', $eventId, $attendance, $waste, $crowding);
            $predStmt->execute();
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Seeded database with {$inserted} events",
        'inserted' => $inserted,
        'action' => 'created'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

<?php
header('Content-Type: application/json');
require_once __DIR__ . '/backend/db.php';

$db = get_db();
$action = $_GET['action'] ?? '';

if ($action === 'insert_test_festival') {
    $stmt = $db->prepare('INSERT INTO festivals_events (name, description, date_start, date_end, location, image) VALUES (?, ?, ?, ?, ?, ?)');
    $name = 'Test Festival ' . date('Y-m-d H:i:s');
    $desc = 'This is a test festival to verify the data loading system is working';
    $start = '2026-03-20';
    $end = '2026-03-21';
    $location = 'Legazpi City, Albay';
    $image = 'https://via.placeholder.com/600x400?text=Test+Festival';
    
    $stmt->bind_param('ssssss', $name, $desc, $start, $end, $location, $image);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Test festival inserted', 'id' => $db->insert_id, 'name' => $name]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}

if ($action === 'insert_test_experience') {
    $stmt = $db->prepare('INSERT INTO local_experiences (title, description, type, price, duration, image) VALUES (?, ?, ?, ?, ?, ?)');
    $title = 'Test Experience ' . date('Y-m-d H:i:s');
    $desc = 'This is a test experience to verify the data loading system';
    $type = 'Adventure';
    $price = 50.00;
    $duration = '2 hours';
    $image = 'https://via.placeholder.com/600x400?text=Test+Experience';
    
    $stmt->bind_param('ssdsss', $title, $desc, $price, $duration, $type, $image);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Test experience inserted', 'id' => $db->insert_id, 'title' => $title]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}

if ($action === 'clear_test_data') {
    $db->query('DELETE FROM festivals_events WHERE name LIKE "%Test Festival%"');
    $db->query('DELETE FROM local_experiences WHERE title LIKE "%Test Experience%"');
    echo json_encode(['success' => true, 'message' => 'Test data cleared']);
    exit;
}

if ($action === 'show_all_festivals') {
    $res = $db->query('SELECT * FROM festivals_events');
    $festivals = [];
    while ($row = $res->fetch_assoc()) {
        $festivals[] = $row;
    }
    echo json_encode(['festivals' => $festivals]);
    exit;
}

if ($action === 'show_all_experiences') {
    $res = $db->query('SELECT * FROM local_experiences');
    $experiences = [];
    while ($row = $res->fetch_assoc()) {
        $experiences[] = $row;
    }
    echo json_encode(['experiences' => $experiences]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>

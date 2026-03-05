<?php
header('Content-Type: application/json');
// Simple migration trigger - for admin use only
require_once 'db.php';
ensure_tables();

function ensure_tables(){
    $db = get_db();
    $results = [];
    
    // Create destinations table
    $sql1 = 'CREATE TABLE IF NOT EXISTS destinations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description LONGTEXT,
        location VARCHAR(255),
        image VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    if($db->query($sql1)) $results[] = 'destinations table OK'; else $results[] = 'destinations ERROR: '.$db->error;
    
    // Create local_experiences table
    $sql2 = 'CREATE TABLE IF NOT EXISTS local_experiences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description LONGTEXT,
        type VARCHAR(255),
        price DECIMAL(10,2),
        duration VARCHAR(255),
        image VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    if($db->query($sql2)) $results[] = 'local_experiences table OK'; else $results[] = 'local_experiences ERROR: '.$db->error;
    
    // Create festivals_events table
    $sql3 = 'CREATE TABLE IF NOT EXISTS festivals_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description LONGTEXT,
        location VARCHAR(255),
        date_start DATE,
        date_end DATE,
        image VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    if($db->query($sql3)) $results[] = 'festivals_events table OK'; else $results[] = 'festivals_events ERROR: '.$db->error;
    
    return $results;
}

$res = ensure_tables();
echo json_encode(['success' => true, 'results' => $res]);
?>

<?php
require_once __DIR__ . '/db.php';
$db = get_db();

$tables = ['destinations', 'local_experiences', 'festivals_events'];
$results = [];

// Destinations table
if ($db->query('CREATE TABLE IF NOT EXISTS destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  location VARCHAR(255),
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)') === TRUE) {
    $results[] = 'destinations table created/verified';
} else {
    $results[] = 'Error with destinations: ' . $db->error;
}

// Local Experiences table
if ($db->query('CREATE TABLE IF NOT EXISTS local_experiences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  type VARCHAR(100),
  price DECIMAL(10, 2),
  duration VARCHAR(100),
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)') === TRUE) {
    $results[] = 'local_experiences table created/verified';
} else {
    $results[] = 'Error with local_experiences: ' . $db->error;
}

// Festivals & Events table
if ($db->query('CREATE TABLE IF NOT EXISTS festivals_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  date_start DATETIME,
  date_end DATETIME,
  location VARCHAR(255),
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)') === TRUE) {
    $results[] = 'festivals_events table created/verified';
} else {
    $results[] = 'Error with festivals_events: ' . $db->error;
}

json_encode(['success' => true, 'messages' => $results]);
echo json_encode(['success' => true, 'messages' => $results]);

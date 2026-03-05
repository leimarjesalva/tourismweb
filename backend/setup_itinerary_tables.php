<?php
/**
 * Setup script to create itinerary management tables
 * Run this once to initialize the database
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    $db = get_db();
    
    // Create itinerary_destinations table
    $sql1 = "CREATE TABLE IF NOT EXISTS itinerary_destinations (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      category VARCHAR(100),
      latitude DOUBLE NOT NULL,
      longitude DOUBLE NOT NULL,
      description TEXT,
      activities TEXT,
      image VARCHAR(255),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql1);
    
    // Create itinerary_hotels table
    $sql2 = "CREATE TABLE IF NOT EXISTS itinerary_hotels (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      category VARCHAR(100),
      latitude DOUBLE NOT NULL,
      longitude DOUBLE NOT NULL,
      rating DECIMAL(3,1),
      rate_per_night INT,
      phone VARCHAR(50),
      address TEXT,
      description TEXT,
      features TEXT,
      image VARCHAR(255),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($sql2);
    
    echo json_encode([
        'success' => true,
        'message' => 'Itinerary management tables created successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

<?php
/**
 * Migration: Add latitude/longitude columns to shops table
 * Run once: php migrate_shop_hotel_coords.php
 */
require_once __DIR__ . '/db.php';
$db = get_db();

echo "=== Shop & Hotel Coordinates Migration ===\n\n";

// Add latitude/longitude to shops table
$cols = $db->query("SHOW COLUMNS FROM shops LIKE 'latitude'");
if ($cols->num_rows === 0) {
    $db->query("ALTER TABLE shops ADD COLUMN latitude DOUBLE DEFAULT NULL AFTER image");
    $db->query("ALTER TABLE shops ADD COLUMN longitude DOUBLE DEFAULT NULL AFTER latitude");
    echo "✅ Added latitude/longitude columns to shops table\n";
} else {
    echo "ℹ️ shops table already has latitude/longitude columns\n";
}

// Ensure itinerary_hotels table exists
$check = $db->query("SHOW TABLES LIKE 'itinerary_hotels'");
if ($check->num_rows === 0) {
    $db->query("CREATE TABLE itinerary_hotels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100) DEFAULT NULL,
        latitude DOUBLE NOT NULL,
        longitude DOUBLE NOT NULL,
        rating DECIMAL(3,1) DEFAULT 4.0,
        rate_per_night INT DEFAULT 0,
        phone VARCHAR(50) DEFAULT NULL,
        address TEXT DEFAULT NULL,
        description TEXT DEFAULT NULL,
        features TEXT DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✅ Created itinerary_hotels table\n";
} else {
    echo "ℹ️ itinerary_hotels table already exists\n";
}

echo "\n✅ Migration complete!\n";

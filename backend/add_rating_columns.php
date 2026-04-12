<?php
require_once('db.php');

$conn = get_db();

echo "Adding rating columns to database tables...\n";

// Add average_rating to destinations table
$query1 = "ALTER TABLE destinations ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00";
if($conn->query($query1)) {
    echo "✅ Added average_rating column to destinations table\n";
} else {
    echo "❌ Failed to add average_rating to destinations: " . $conn->error . "\n";
}

// Add rating to shops table
$query2 = "ALTER TABLE shops ADD COLUMN IF NOT EXISTS rating DECIMAL(3,2) DEFAULT 0.00";
if($conn->query($query2)) {
    echo "✅ Added rating column to shops table\n";
} else {
    echo "❌ Failed to add rating to shops: " . $conn->error . "\n";
}

// Add rating to products table
$query3 = "ALTER TABLE products ADD COLUMN IF NOT EXISTS rating DECIMAL(3,2) DEFAULT 0.00";
if($conn->query($query3)) {
    echo "✅ Added rating column to products table\n";
} else {
    echo "❌ Failed to add rating to products: " . $conn->error . "\n";
}

// Check if hotels table exists, if not create it
$query4 = "CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if($conn->query($query4)) {
    echo "✅ Created/verified hotels table\n";
} else {
    echo "❌ Failed to create hotels table: " . $conn->error . "\n";
}

$conn->close();
echo "Database schema update completed!\n";
?>
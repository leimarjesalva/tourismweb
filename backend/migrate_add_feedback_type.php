<?php
/**
 * Migration: Add feedback_type column to feedback table
 * Description: Adds a feedback_type column to store the type of feedback (suggestion, bug, praise, other)
 */

require_once 'db.php';

try {
    $conn = get_db();
    
    // Check if column already exists
    $checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='feedback' AND COLUMN_NAME='feedback_type'";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows > 0) {
        echo "✅ Column 'feedback_type' already exists\n";
        exit;
    }
    
    // Add the column
    $migrationSql = "ALTER TABLE feedback ADD COLUMN feedback_type VARCHAR(50) DEFAULT 'other' AFTER user_name";
    
    if ($conn->query($migrationSql)) {
        echo "✅ Successfully added feedback_type column to feedback table\n";
        
        // Log the migration
        error_log("Migration complete: Added feedback_type column to feedback table");
        
    } else {
        echo "❌ Error adding column: " . $conn->error . "\n";
        error_log("Migration failed: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "❌ Migration error: " . $e->getMessage() . "\n";
    error_log("Migration error: " . $e->getMessage());
}
?>

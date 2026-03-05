<?php
/**
 * Migration: Add image column to feedback table
 * Run this once to update existing databases
 */
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    $db = get_db();
    
    // Check if image column already exists
    $res = $db->query("SHOW COLUMNS FROM feedback LIKE 'image'");
    if ($res && $res->num_rows > 0) {
        j(['already_migrated' => true, 'message' => 'image column already exists']);
    }
    
    // Add image column
    $sql = "ALTER TABLE feedback ADD COLUMN image VARCHAR(255) DEFAULT NULL";
    if ($db->query($sql)) {
        j(['success' => true, 'message' => 'Successfully added image column to feedback table']);
    } else {
        j(['success' => false, 'error' => $db->error]);
    }
} catch (Exception $e) {
    j(['success' => false, 'error' => $e->getMessage()]);
}

function j($v) {
    echo json_encode($v);
    exit;
}
?>

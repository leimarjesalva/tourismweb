<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$r = $db->query("SHOW COLUMNS FROM events LIKE 'entrance_fee'");
if ($r->num_rows > 0) {
    echo "Column already exists\n";
} else {
    $db->query("ALTER TABLE events ADD COLUMN entrance_fee DECIMAL(10,2) DEFAULT NULL");
    echo "Column entrance_fee added\n";
}
$db->close();

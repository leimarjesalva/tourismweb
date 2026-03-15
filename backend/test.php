<?php
echo "PHP OK";
$db = new mysqli(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    getenv('MYSQLPORT')
);
if ($db->connect_errno) {
    echo " | DB ERROR: " . $db->connect_error;
} else {
    echo " | DB OK";
    $result = $db->query("SHOW TABLES");
    $tables = [];
    while($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    echo " | Tables: " . implode(', ', $tables);
}
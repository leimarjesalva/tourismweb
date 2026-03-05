<?php
require 'backend/db.php';
$db = get_db();
$res = $db->query('SELECT * FROM itineraries');
while($r = $res->fetch_assoc()) {
    echo json_encode($r) . "\n";
}
?>
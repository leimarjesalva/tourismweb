<?php
require_once 'backend/db.php';
$db = get_db();
if (!$db) { echo "no db\n"; exit; }
$res = $db->query('SELECT id,title,image FROM events LIMIT 3');
if (!$res) { echo "query failed " . $db->error; exit; }
while($r = $res->fetch_assoc()){
    echo json_encode($r) . "\n";
}

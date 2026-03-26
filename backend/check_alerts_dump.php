<?php
require_once 'db.php';
$db = get_db();
$res = $db->query('SELECT * FROM event_alerts');
if(!$res){
    echo 'err='.$db->error.PHP_EOL;
    die();
}
while($r=$res->fetch_assoc()){
    echo json_encode($r).PHP_EOL;
}
?>
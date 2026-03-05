<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
$db = get_db();

$cols = ['start_lat','start_lng','end_lat','end_lng'];
$added = [];
$existing = [];
foreach($cols as $c){
    $res = $db->query("SHOW COLUMNS FROM events LIKE '".$db->real_escape_string($c)."'");
    if ($res && $res->num_rows>0){
        $existing[] = $c;
        continue;
    }
    // choose type double nullable
    $sql = "ALTER TABLE events ADD COLUMN {$c} DOUBLE NULL";
    if ($db->query($sql)){
        $added[] = $c;
    } else {
        // if failed, capture error but continue
        error_log("Failed adding column {$c}: " . $db->error);
    }
}

echo json_encode(['success'=>true,'added'=>$added,'existing'=>$existing]);
exit;

?>
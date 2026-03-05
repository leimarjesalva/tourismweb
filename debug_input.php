<?php
// simple debug page to echo raw body and $_POST
header('Content-Type: application/json');
$raw = file_get_contents('php://input');
$decoded = json_decode($raw, true);
echo json_encode(['raw'=>$raw, 'decoded'=>$decoded, '_POST'=>$_POST]);
?>
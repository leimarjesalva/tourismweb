<?php
require_once 'backend/api.php';
$tests = [
    'backend/uploads/destination_test.jpg',
    '../backend/uploads/destination_test.jpg',
    'http://example.com/foo.png',
    'img/uploads/destination_test.jpg',
];
foreach($tests as $t){ echo "$t -> " . normalize_image_path($t) . "\n"; }

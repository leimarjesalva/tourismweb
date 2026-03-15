<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route backend PHP files
if (strpos($uri, '/backend/') === 0) {
    $file = '/app' . $uri;
    if (file_exists($file)) {
        chdir(dirname($file));
        require $file;
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File not found: ' . $file]);
        exit;
    }
}

// Serve frontend files
$file = '/app/frontend' . $uri;
if ($uri === '/' || !file_exists($file)) {
    include '/app/frontend/index.html';
    exit;
}
return false;
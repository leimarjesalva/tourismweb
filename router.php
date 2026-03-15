<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route backend PHP files
if (strpos($uri, '/backend/') === 0) {
    $file = '/app' . $uri;
    if (file_exists($file)) {
        chdir(dirname($file));
        require $file;
        exit;
    }
    http_response_code(404);
    echo json_encode(['error' => 'Not found: ' . $uri]);
    exit;
}

// Serve frontend static files
$frontendFile = '/app/frontend' . $uri;
if (file_exists($frontendFile) && !is_dir($frontendFile)) {
    return false; // Let PHP built-in server handle it
}

// Default - serve index.html
include '/app/frontend/index.html';
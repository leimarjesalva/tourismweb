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

// Route img/uploads to frontend/img/uploads
if (strpos($uri, '/img/') === 0) {
    $file = '/app/frontend' . $uri;
    if (file_exists($file)) {
        return false;
    }
}

// Serve frontend static files
$frontendFile = '/app/frontend' . $uri;
if (file_exists($frontendFile) && !is_dir($frontendFile)) {
    return false;
}

// Default - serve index.html
include '/app/frontend/index.html';
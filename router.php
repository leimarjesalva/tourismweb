<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route backend PHP files only
if (strpos($uri, '/backend/') === 0 && substr($uri, -4) === '.php') {
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

// Serve backend static files (images, etc.)
if (strpos($uri, '/backend/uploads/') === 0) {
    $file = '/app' . $uri;
    if (file_exists($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'gif' => 'image/gif',
            'mp4' => 'video/mp4', 'webp' => 'image/webp'
        ];
        header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

// Serve frontend static files
$frontendFile = '/app/frontend' . $uri;
if (file_exists($frontendFile) && !is_dir($frontendFile)) {
    return false;
}

// Default - serve index.html
include '/app/frontend/index.html';
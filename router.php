<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Remove query string
$uri = strtok($uri, '?');

// Route backend PHP files
if (strpos($uri, '/backend/') === 0 && substr($uri, -4) === '.php') {
    $file = '/app' . $uri;
    if (file_exists($file)) {
        chdir(dirname($file));
        require $file;
        exit;
    }
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

// Serve ALL static files from frontend
$frontendFile = '/app/frontend' . $uri;
if (file_exists($frontendFile) && !is_dir($frontendFile)) {
    $ext = strtolower(pathinfo($frontendFile, PATHINFO_EXTENSION));
    $mime = [
        'html' => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'geojson' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'mp4'  => 'video/mp4',
        'ico'  => 'image/x-icon',
        'svg'  => 'image/svg+xml',
    ];
    header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
    readfile($frontendFile);
    exit;
}

// Serve backend uploads
if (strpos($uri, '/backend/uploads/') === 0) {
    $file = '/app' . $uri;
    if (file_exists($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'mp4' => 'video/mp4',
        ];
        header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

// Default - serve index.html
include '/app/frontend/index.html';
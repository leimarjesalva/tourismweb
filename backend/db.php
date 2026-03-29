<?php
error_reporting(0);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Fix session persistence on Railway
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

session_start();

define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'capstone_db');
define('DB_PORT', getenv('MYSQLPORT') ?: 3306);


define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');


define('GOOGLE_CLIENT_ID', '592851137026-ojducpgk2od9rvtob47sn5k5fktqvi6h.apps.googleusercontent.com');

function get_db(){
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($mysqli->connect_errno) {
        http_response_code(500);
        echo json_encode(['error' => 'DB connect error: '.$mysqli->connect_error]);
        exit;
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function require_admin(){
    if (empty($_SESSION['is_admin'])){
        ob_end_clean();
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error'=>'unauthorized', 'session_data' => $_SESSION]);
        exit;
    }
}

function j($data){
    ob_end_clean();
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function json_input(){
    $data = json_decode(file_get_contents('php://input'), true);
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        // If JSON was parsed without error, return it (even if empty array/object).
        // Otherwise fall back to $_POST for form-encoded submissions.
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
        return $_POST;
}

?>

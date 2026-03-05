<?php
// Verifies an ID token issued by Google Identity Services
// POST { id_token: '...' }
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id_token = $data['id_token'] ?? '';

if (!$id_token){
    http_response_code(400);
    echo json_encode(['error'=>'missing id_token']);
    exit;
}

$url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($id_token);
$resp = @file_get_contents($url);
if ($resp === false){
    http_response_code(502);
    echo json_encode(['error'=>'unable to verify token']);
    exit;
}

$info = json_decode($resp, true);

// Validate audience (must match configured client ID)
if (!isset($info['aud']) || $info['aud'] !== GOOGLE_CLIENT_ID){
    http_response_code(401);
    echo json_encode(['error'=>'invalid_client','info'=>$info]);
    exit;
}

if (isset($info['email'])){
    // Upsert user into `users` table
    try {
        $mysqli = get_db();
        $email = $info['email'];
        $name = $info['name'] ?? null;

        $stmt = $mysqli->prepare("INSERT INTO users (email, name, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name)");
        if ($stmt){
            $stmt->bind_param('ss', $email, $name);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(['success'=>true,'email'=>$email,'name'=>$name]);
    } catch (Exception $e){
        http_response_code(500);
        echo json_encode(['error'=>'db_error','message'=>$e->getMessage()]);
    }
} else {
    http_response_code(401);
    echo json_encode(['success'=>false,'info'=>$info]);
}

?>

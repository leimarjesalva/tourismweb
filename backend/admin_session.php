<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');


if ($action = $_GET['action'] ?? ($_POST['action'] ?? null)){
    
    if ($action === 'check_session'){
        if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true){
            echo json_encode(['logged_in'=>true, 'username'=>$_SESSION['admin_user']??'admin']);
        } else {
            echo json_encode(['logged_in'=>false]);
        }
        exit;
    }
    
    if ($action === 'logout'){
        session_destroy();
        echo json_encode(['success'=>true]);
        exit;
    }
}


http_response_code(400);
echo json_encode(['error'=>'unknown action']);

?>

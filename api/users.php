<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/controllers/UserController.php';

$action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD']==='GET' ? 'list' : 'create');
try {
    switch ($action) {
        case 'list':
            $rows = UserController::getAll();
            echo json_encode(['success'=>true,'data'=>$rows]);
            break;
        case 'create':
            $ok = UserController::create($_POST['branch_id'] ?? null, $_POST['role'] ?? 'staff', $_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? 'password', $_POST['mobile'] ?? '', $_POST['is_part_time'] ?? 0);
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'delete':
            require_once __DIR__ . '/../config/db.php';
            $id = intval($_POST['id'] ?? 0);
            $res = mysqli_query($conn, "DELETE FROM users WHERE id = $id");
            echo json_encode(['success'=>(bool)$res]);
            break;
        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
} catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }


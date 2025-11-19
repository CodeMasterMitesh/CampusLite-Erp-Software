<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/controllers/BranchController.php';

$action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD']==='GET' ? 'list' : 'create');
try {
    switch ($action) {
        case 'list':
            $rows = BranchController::getAll();
            echo json_encode(['success'=>true,'data'=>$rows]);
            break;
        case 'create':
            $ok = BranchController::create($_POST['name'] ?? '', $_POST['address'] ?? '');
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'delete':
            require_once __DIR__ . '/../config/db.php';
            $id = intval($_POST['id'] ?? 0);
            $res = mysqli_query($conn, "DELETE FROM branches WHERE id = $id");
            echo json_encode(['success'=>(bool)$res]);
            break;
        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
} catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }


<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/controllers/CourseController.php';

$action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD']==='GET' ? 'list' : 'create');
try {
    switch ($action) {
        case 'list':
            $rows = CourseController::getAll();
            echo json_encode(['success'=>true,'data'=>$rows]);
            break;
        case 'create':
            $ok = CourseController::create(intval($_POST['branch_id'] ?? 0), $_POST['title'] ?? '', $_POST['description'] ?? '', floatval($_POST['total_fee'] ?? 0), intval($_POST['duration_months'] ?? 0));
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'delete':
            require_once __DIR__ . '/../config/db.php';
            $id = intval($_POST['id'] ?? 0);
            $res = mysqli_query($conn, "DELETE FROM courses WHERE id = $id");
            echo json_encode(['success'=>(bool)$res]);
            break;
        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
} catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }


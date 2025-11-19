<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/controllers/SubjectController.php';

$action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD']==='GET' ? 'list' : 'create');
try {
    switch ($action) {
        case 'list':
            $rows = SubjectController::getAll();
            echo json_encode(['success'=>true,'data'=>$rows]);
            break;
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            // SubjectController doesn't have get() — use getAll filter locally
            $rows = SubjectController::getAll();
            $found = null;
            foreach ($rows as $r) if ($r['id']===$id) $found=$r;
            echo json_encode(['success'=>true,'data'=>$found]);
            break;
        case 'create':
            $ok = SubjectController::create($_POST['title'] ?? '', $_POST['description'] ?? '');
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            // fallback: direct delete
            require_once __DIR__ . '/../config/db.php';
            $res = mysqli_query($conn, "DELETE FROM subjects WHERE id = $id");
            echo json_encode(['success'=>(bool)$res]);
            break;
        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
} catch(Exception $e){ echo json_encode(['success'=>false,'error'=>$e->getMessage()]); }


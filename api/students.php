<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../app/controllers/StudentController.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? ($method === 'GET' ? 'list' : 'create');

try {
    switch ($action) {
        case 'list':
            $rows = StudentController::getAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            break;
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            $row = StudentController::get($id);
            echo json_encode(['success' => true, 'data' => $row]);
            break;
        case 'create':
            $data = $_POST;
            $ok = StudentController::create($data);
            echo json_encode(['success' => (bool)$ok]);
            break;
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $data = $_POST;
            $ok = StudentController::update($id, $data);
            echo json_encode(['success' => (bool)$ok]);
            break;
        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            $ok = StudentController::delete($id);
            echo json_encode(['success' => (bool)$ok]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

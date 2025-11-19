<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../app/controllers/StudentController.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? ($method === 'GET' ? 'list' : 'create');

try {
    switch ($action) {
        case 'list':
            $rows = StudentController::getAll();
            send_json(true, null, $rows);
            break;
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            $row = StudentController::get($id);
            if ($row) send_json(true, null, $row);
            else send_json(false, 'Student not found');
            break;
        case 'create':
            $data = $_POST;
            $ok = StudentController::create($data);
            if ($ok) send_json(true, 'Student created');
            else send_json(false, 'Failed to create student');
            break;
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $data = $_POST;
            $ok = StudentController::update($id, $data);
            if ($ok) send_json(true, 'Student updated');
            else send_json(false, 'Failed to update student');
            break;
        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            $ok = StudentController::delete($id);
            if ($ok) send_json(true, 'Student deleted');
            else send_json(false, 'Failed to delete student');
            break;
        default:
            send_json(false, 'Unknown action');
    }
} catch (Exception $e) {
    send_json(false, 'Server error', null, ['exception' => $e->getMessage()]);
}

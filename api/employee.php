<?php

use CampusLite\Controllers\EmployeeController;

require_once __DIR__ . '/init.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? ($_SERVER['REQUEST_METHOD']==='GET' ? 'list' : 'create');
try {
    switch ($action) {
        case 'list':
            $rows = EmployeeController::getAll();
            // Ensure dob is present for birthday reminders
            foreach ($rows as &$row) {
                if (!isset($row['dob'])) $row['dob'] = $row['dob'] ?? '';
            }
            echo json_encode(['success'=>true,'data'=>$rows]);
            break;
        case 'get':
            $id = intval($_GET['id'] ?? 0);
            $row = EmployeeController::get($id);
            echo json_encode(['success'=> (bool)$row, 'data'=>$row]);
            break;
        case 'create':
            $data = $_POST;
            // handle profile photo upload
            if (!empty($_FILES['profile_photo']['name'])) {
                $dir = __DIR__ . '/../public/uploads/employees';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $safe = uniqid('emp_') . '.' . strtolower($ext);
                $dest = $dir . '/' . $safe;
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)) {
                    $data['profile_photo'] = $safe;
                }
            }
            // decode nested arrays if provided as JSON strings
            foreach (['education','employment'] as $k) {
                if (isset($data[$k]) && is_string($data[$k])) {
                    $dec = json_decode($data[$k], true);
                    if (is_array($dec)) $data[$k] = $dec;
                }
            }
            $ok = EmployeeController::create($data);
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            $data = $_POST;
            // Preserve existing document fields if not re-uploaded
            $existing = EmployeeController::get($id);
            if (!empty($_FILES['profile_photo']['name'])) {
                $dir = __DIR__ . '/../public/uploads/employees';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $safe = uniqid('emp_') . '.' . strtolower($ext);
                $dest = $dir . '/' . $safe;
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)) {
                    $data['profile_photo'] = $safe;
                }
            } else if ($existing && isset($existing['profile_photo'])) {
                $data['profile_photo'] = $existing['profile_photo'];
            }
            // Handle ID document uploads: aadhar_card, pan_card, passport
            $docDir = __DIR__ . '/../public/uploads/employees';
            if (!is_dir($docDir)) @mkdir($docDir, 0777, true);
            foreach (['aadhar_card','pan_card','passport'] as $doc) {
                if (!empty($_FILES[$doc]['name'])) {
                    $ext = pathinfo($_FILES[$doc]['name'], PATHINFO_EXTENSION);
                    if ($ext === '') $ext = 'bin';
                    $safe = uniqid($doc.'_') . '.' . strtolower($ext);
                    $dest = $docDir . '/' . $safe;
                    if (move_uploaded_file($_FILES[$doc]['tmp_name'], $dest)) {
                        $data[$doc] = $safe;
                    }
                } else if ($existing && isset($existing[$doc])) {
                    $data[$doc] = $existing[$doc];
                }
            }
            foreach (['education','employment'] as $k) {
                if (isset($data[$k]) && is_string($data[$k])) {
                    $dec = json_decode($data[$k], true);
                    if (is_array($dec)) $data[$k] = $dec;
                }
            }
            $ok = EmployeeController::update($id, $data);
            echo json_encode(['success'=>(bool)$ok]);
            break;
        case 'delete-photo':
            $id = intval($_POST['id'] ?? 0);
            $row = EmployeeController::get($id);
            if ($row && !empty($row['profile_photo'])) {
                $path = __DIR__ . '/../public/uploads/employees/' . basename($row['profile_photo']);
                if (is_file($path)) @unlink($path);
                // clear field
                $_POST = ['profile_photo' => null];
                EmployeeController::update($id, $_POST);
                echo json_encode(['success'=>true]);
            } else {
                echo json_encode(['success'=>false, 'message'=>'No photo']);
            }
            break;
        case 'delete':
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
            $ok = EmployeeController::delete($id);
            echo json_encode(['success'=>(bool)$ok]);
            break;
        default:
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}

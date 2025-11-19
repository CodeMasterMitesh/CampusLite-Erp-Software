<?php
// api/leaves.php
require_once __DIR__ . '/../config/db.php';
$action = $_GET['action'] ?? '';
header('Content-Type: application/json');
if ($action === 'apply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $branch_id = $_POST['branch_id'] ?? null;
    $leave_type = $_POST['leave_type'] ?? '';
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO leaves (user_id, branch_id, leave_type, from_date, to_date, reason) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iissss', $user_id, $branch_id, $leave_type, $from_date, $to_date, $reason);
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
    exit;
}
if ($action === 'decide' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_id = $_POST['leave_id'] ?? null;
    $status = $_POST['status'] ?? '';
    $decided_by = $_POST['decided_by'] ?? null;
    $stmt = mysqli_prepare($conn, "UPDATE leaves SET status = ?, decided_by = ?, decided_on = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $status, $decided_by, $leave_id);
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

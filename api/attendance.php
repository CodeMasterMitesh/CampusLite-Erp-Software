<?php
// api/attendance.php
require_once __DIR__ . '/../config/db.php';
$action = $_GET['action'] ?? '';
header('Content-Type: application/json');
if ($action === 'mark' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = $_POST['branch_id'] ?? null;
    $entity_type = $_POST['entity_type'] ?? '';
    $entity_id = $_POST['entity_id'] ?? null;
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? '';
    $note = $_POST['note'] ?? '';
    $recorded_by = $_POST['recorded_by'] ?? null;
    $stmt = mysqli_prepare($conn, "INSERT INTO attendance (branch_id, entity_type, entity_id, date, status, note, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isisssi', $branch_id, $entity_type, $entity_id, $date, $status, $note, $recorded_by);
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
    exit;
}
if ($action === 'report') {
    $branch_id = $_GET['branch_id'] ?? null;
    $from = $_GET['from'] ?? null;
    $to = $_GET['to'] ?? null;
    $sql = "SELECT * FROM attendance WHERE 1=1";
    if ($branch_id) $sql .= " AND branch_id = " . intval($branch_id);
    if ($from) $sql .= " AND date >= '" . mysqli_real_escape_string($conn, $from) . "'";
    if ($to) $sql .= " AND date <= '" . mysqli_real_escape_string($conn, $to) . "'";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    echo json_encode(['success' => true, 'attendance' => $rows]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

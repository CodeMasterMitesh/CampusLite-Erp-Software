<?php
// api/courses.php
require_once __DIR__ . '/../config/db.php';
$action = $_GET['action'] ?? '';
header('Content-Type: application/json');
if ($action === 'list') {
    $branch_id = $_GET['branch_id'] ?? null;
    $sql = "SELECT * FROM courses";
    if ($branch_id) {
        $sql .= " WHERE branch_id = " . intval($branch_id);
    }
    $result = mysqli_query($conn, $sql);
    $courses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
    echo json_encode(['success' => true, 'courses' => $courses]);
    exit;
}
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = $_POST['branch_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $total_fee = $_POST['total_fee'] ?? 0;
    $duration_months = $_POST['duration_months'] ?? 0;
    $stmt = mysqli_prepare($conn, "INSERT INTO courses (branch_id, title, description, total_fee, duration_months) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issdi', $branch_id, $title, $description, $total_fee, $duration_months);
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

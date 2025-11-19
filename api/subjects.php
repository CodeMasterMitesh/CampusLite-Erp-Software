<?php
// api/subjects.php
require_once __DIR__ . '/../config/db.php';
$action = $_GET['action'] ?? '';
header('Content-Type: application/json');
if ($action === 'list') {
    $result = mysqli_query($conn, "SELECT * FROM subjects");
    $subjects = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = $row;
    }
    echo json_encode(['success' => true, 'subjects' => $subjects]);
    exit;
}
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO subjects (title, description) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $title, $description);
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

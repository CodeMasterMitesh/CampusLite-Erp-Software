<?php
// api/branches.php
require_once __DIR__ . '/../config/db.php';
$action = $_GET['action'] ?? '';
header('Content-Type: application/json');
if ($action === 'list') {
    $result = mysqli_query($conn, "SELECT * FROM branches");
    $branches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $branches[] = $row;
    }
    echo json_encode(['success' => true, 'branches' => $branches]);
    exit;
}
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO branches (company_id, name, address) VALUES (1, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $name, $address);
    $success = mysqli_stmt_execute($stmt);
    echo json_encode(['success' => $success]);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

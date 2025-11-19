<?php
// api/auth.php
require_once __DIR__ . '/../config/db.php';
session_start();
$action = $_GET['action'] ?? '';
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // exit;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $sql = "SELECT * FROM users WHERE email = ? AND status = 1 LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'role' => $user['role'],
            'branch_id' => $user['branch_id']
        ];
        header('Location: ../index.php?page=dashboard');
        exit;
    } else {
        echo '<script>alert("Invalid credentials");window.location.href="../index.php?page=login";</script>';
        exit;
    }
}
?>

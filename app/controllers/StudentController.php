<?php
// app/controllers/StudentController.php
require_once __DIR__ . '/../../config/db.php';
class StudentController {
    public static function getAll($branch_id = null) {
        global $conn;
        $sql = "SELECT * FROM students";
        if ($branch_id) $sql .= " WHERE branch_id = " . intval($branch_id);
        $res = mysqli_query($conn, $sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        return $rows;
    }
    public static function get($id) {
        global $conn;
        $id = intval($id);
        $res = mysqli_query($conn, "SELECT * FROM students WHERE id = $id LIMIT 1");
        return mysqli_fetch_assoc($res) ?: null;
    }
    public static function create($data) {
        global $conn;
        $stmt = mysqli_prepare($conn, "INSERT INTO students (branch_id, name, email, mobile, dob, father_name, address, registration_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssssssi', $data['branch_id'], $data['name'], $data['email'], $data['mobile'], $data['dob'], $data['father_name'], $data['address'], $data['registration_date'], $data['status']);
        return mysqli_stmt_execute($stmt);
    }
    public static function update($id, $data) {
        global $conn;
        $stmt = mysqli_prepare($conn, "UPDATE students SET name=?, email=?, mobile=?, dob=?, father_name=?, address=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssssii', $data['name'], $data['email'], $data['mobile'], $data['dob'], $data['father_name'], $data['address'], $data['status'], $id);
        return mysqli_stmt_execute($stmt);
    }
    public static function delete($id) {
        global $conn;
        $id = intval($id);
        return mysqli_query($conn, "DELETE FROM students WHERE id = $id");
    }
}
?>

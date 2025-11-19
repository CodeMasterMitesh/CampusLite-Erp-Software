<?php
// app/controllers/AttendanceController.php
require_once __DIR__ . '/../../config/db.php';
class AttendanceController {
    public static function getAll($branch_id = null) {
        global $conn;
        $sql = "SELECT * FROM attendance";
        if ($branch_id) $sql .= " WHERE branch_id = " . intval($branch_id);
        $res = mysqli_query($conn, $sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        return $rows;
    }
    public static function record($data) {
        global $conn;
        $stmt = mysqli_prepare($conn, "INSERT INTO attendance (branch_id, entity_type, entity_id, date, status, note, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isisssi', $data['branch_id'], $data['entity_type'], $data['entity_id'], $data['date'], $data['status'], $data['note'], $data['recorded_by']);
        return mysqli_stmt_execute($stmt);
    }
    public static function delete($id) {
        global $conn;
        $id = intval($id);
        return mysqli_query($conn, "DELETE FROM attendance WHERE id = $id");
    }
}
?>

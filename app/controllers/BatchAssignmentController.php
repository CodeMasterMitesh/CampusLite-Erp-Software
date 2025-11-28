<?php
if (!defined('APP_INIT')) { http_response_code(403); exit('Forbidden'); }
// app/controllers/BatchAssignmentController.php
require_once __DIR__ . '/../../config/db.php';
class BatchAssignmentController {
    public static function getAll() {
        global $conn;
        $sql = "SELECT * FROM batch_assignments ORDER BY assigned_at DESC";
        $res = mysqli_query($conn, $sql);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) {
            // decode JSON fields
            if (isset($r['subjects']) && $r['subjects'] !== null) {
                $decoded = json_decode($r['subjects'], true);
                $r['subjects'] = is_array($decoded) ? $decoded : [];
            } else { $r['subjects'] = []; }
            if (isset($r['students_ids']) && $r['students_ids'] !== null) {
                $decoded = json_decode($r['students_ids'], true);
                $r['students_ids'] = is_array($decoded) ? $decoded : [];
            } else { $r['students_ids'] = []; }
            $rows[] = $r;
        }
        return $rows;
    }
    public static function get($id) {
        global $conn;
        $id = intval($id);
        $res = mysqli_query($conn, "SELECT * FROM batch_assignments WHERE id = $id LIMIT 1");
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            if (isset($row['subjects']) && $row['subjects'] !== null) {
                $decoded = json_decode($row['subjects'], true);
                $row['subjects'] = is_array($decoded) ? $decoded : [];
            } else { $row['subjects'] = []; }
            if (isset($row['students_ids']) && $row['students_ids'] !== null) {
                $decoded = json_decode($row['students_ids'], true);
                $row['students_ids'] = is_array($decoded) ? $decoded : [];
            } else { $row['students_ids'] = []; }
        }
        return $row;
    }
    public static function create($data) {
        global $conn;
        $subjectsJson = null;
        if (!empty($data['subjects'])) {
            if (is_array($data['subjects'])) $subjectsJson = json_encode(array_values($data['subjects'])); else $subjectsJson = json_encode([$data['subjects']]);
        }
        $studentsIdsJson = null;
        if (!empty($data['students_ids'])) {
            if (is_array($data['students_ids'])) $studentsIdsJson = json_encode(array_values($data['students_ids'])); else $studentsIdsJson = json_encode([$data['students_ids']]);
        }
        $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
        $stmt = mysqli_prepare($conn, "INSERT INTO batch_assignments (batch_id, user_id, students_ids, role, subjects, assigned_at) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iissss', $data['batch_id'], $user_id, $studentsIdsJson, $data['role'], $subjectsJson, $data['assigned_at']);
        return mysqli_stmt_execute($stmt);
    }
    public static function update($id, $data) {
        global $conn;
        $subjectsJson = null;
        if (!empty($data['subjects'])) {
            if (is_array($data['subjects'])) $subjectsJson = json_encode(array_values($data['subjects'])); else $subjectsJson = json_encode([$data['subjects']]);
        }
        $studentsIdsJson = null;
        if (!empty($data['students_ids'])) {
            if (is_array($data['students_ids'])) $studentsIdsJson = json_encode(array_values($data['students_ids'])); else $studentsIdsJson = json_encode([$data['students_ids']]);
        }
        $user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
        $stmt = mysqli_prepare($conn, "UPDATE batch_assignments SET batch_id=?, user_id=?, students_ids=?, role=?, subjects=?, assigned_at=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'iissssi', $data['batch_id'], $user_id, $studentsIdsJson, $data['role'], $subjectsJson, $data['assigned_at'], $id);
        return mysqli_stmt_execute($stmt);
    }
    public static function delete($id) {
        global $conn;
        $id = intval($id);
        return mysqli_query($conn, "DELETE FROM batch_assignments WHERE id = $id");
    }
}
?>
<?php
if (!defined('APP_INIT')) { http_response_code(403); exit('Forbidden'); }
// app/controllers/CourseController.php
require_once __DIR__ . '/../../config/db.php';
class CourseController {
    public static function getAll($branch_id = null) {
        global $conn;
        $courses = [];
        if ($branch_id) {
            $stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE branch_id = ?");
            $bid = intval($branch_id);
            mysqli_stmt_bind_param($stmt, 'i', $bid);
            if (mysqli_stmt_execute($stmt)) {
                $res = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($res)) $courses[] = $row;
            }
            return $courses;
        }
        $result = mysqli_query($conn, "SELECT * FROM courses");
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
        return $courses;
    }
    public static function create($branch_id, $title, $description, $total_fee, $duration_months) {
        global $conn;
        $stmt = mysqli_prepare($conn, "INSERT INTO courses (branch_id, title, description, total_fee, duration_months) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'issdi', $branch_id, $title, $description, $total_fee, $duration_months);
        return mysqli_stmt_execute($stmt);
    }
}
?>

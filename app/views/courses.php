<?php
// app/views/courses.php
require_once __DIR__ . '/../controllers/CourseController.php';
$courses = CourseController::getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Courses - Tuition360</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container dashboard-container">
    <h2>Courses</h2>
    <div class="mb-3">
        <button class="btn btn-success btn-export" onclick="exportTable('courses-table')">Export to Excel</button>
        <button class="btn btn-primary btn-print" onclick="printTable('courses-table')">Print</button>
    </div>
    <table class="table table-bordered" id="courses-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Branch</th>
                <th>Title</th>
                <th>Description</th>
                <th>Total Fee</th>
                <th>Duration (months)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars($course['id']) ?></td>
                <td><?= htmlspecialchars($course['branch_id']) ?></td>
                <td><?= htmlspecialchars($course['title']) ?></td>
                <td><?= htmlspecialchars($course['description']) ?></td>
                <td><?= htmlspecialchars($course['total_fee']) ?></td>
                <td><?= htmlspecialchars($course['duration_months']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="assets/js/export-print.js"></script>

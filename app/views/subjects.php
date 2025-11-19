<?php
// app/views/subjects.php
require_once __DIR__ . '/../controllers/SubjectController.php';
$subjects = SubjectController::getAll();
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container dashboard-container">
    <h2>Subjects</h2>
    <div class="mb-3">
        <button class="btn btn-success btn-export" onclick="exportTable('subjects-table')">Export to Excel</button>
        <button class="btn btn-primary btn-print" onclick="printTable('subjects-table')">Print</button>
    </div>
    <table class="table table-bordered" id="subjects-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?= htmlspecialchars($subject['id']) ?></td>
                <td><?= htmlspecialchars($subject['title']) ?></td>
                <td><?= htmlspecialchars($subject['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>

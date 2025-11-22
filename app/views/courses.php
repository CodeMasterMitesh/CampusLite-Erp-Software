<?php
// app/views/courses.php
require_once __DIR__ . '/../controllers/CourseController.php';
$courses = CourseController::getAll();
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container dashboard-container">
    <h2>Courses</h2>
    <div class="mb-3">
        <button class="btn btn-success btn-export" onclick="exportTable('courses-table')">Export to Excel</button>
        <button class="btn btn-primary btn-print" onclick="printTable('courses-table')">Print</button>
    </div>
    <div class="table-responsive table-compact" id="tableContainer">
        <table class="table data-table table-bordered" id="courses-table">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        const table = $('#courses-table');
        const thead = table.find('thead');
        const filterRow = $('<tr>').addClass('filters');
        thead.find('tr').first().children().each(function() {
            const th = $('<th>');
            th.html('<input type="text" class="form-control form-control-sm" placeholder="Search">');
            filterRow.append(th);
        });
        thead.append(filterRow);
        const dataTable = table.DataTable({ dom: 'lrtip', orderCellsTop:true, fixedHeader:true, pageLength:10, lengthMenu:[10,25,50,100], responsive:true });
        $('#courses-table thead').on('keyup change', 'tr.filters input', function(){ const idx=$(this).closest('th').index(); const val=$(this).val(); if (dataTable.column(idx).search()!==val) dataTable.column(idx).search(val).draw(); });
    } catch(e){}
});
</script>

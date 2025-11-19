<?php
// app/views/students.php
$students = [];
$controllerFile = __DIR__ . '/../controllers/StudentController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $cls = 'StudentController';
    if (class_exists($cls) && method_exists($cls, 'getAll')) {
        $students = $cls::getAll();
    }
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$totalStudents = count($students);
$totalPages = 1;
// load branches for dropdown
$branchFile = __DIR__ . '/../controllers/BranchController.php';
$branches = [];
if (file_exists($branchFile)) {
    require_once $branchFile;
    if (class_exists('BranchController') && method_exists('BranchController','getAll')) $branches = BranchController::getAll();
}
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-user-graduate"></i> Students</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fas fa-plus"></i> Add New Student
        </button>
    </div>
    <!-- Table Container -->
    <div class="advanced-table-container">
        <!-- Table Controls -->
        <div class="table-controls">
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search students..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-success btn-action" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button class="btn btn-secondary btn-action" onclick="printTable()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button class="btn btn-info btn-action" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <!-- Table -->
        <div class="table-responsive position-relative" id="tableContainer">
            <table class="table data-table" id="students-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No students found</h4>
                                    <p>No students match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                        <i class="fas fa-plus"></i> Add First Student
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($student['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($student['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($student['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($student['branch'] ?? '') ?></td>
                                <td>
                                    <?php if (isset($student['status'])): ?>
                                        <span class="status-badge <?= $student['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                            <?= ucfirst($student['status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-table" onclick="editStudent(<?= $student['id'] ?? 0 ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewStudent(<?= $student['id'] ?? 0 ?>)" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteStudent(<?= $student['id'] ?? 0 ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                Showing <?= count($students) ?> of <?= $totalStudents ?> students
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=students&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=students&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=students&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <input type="hidden" name="id" id="studentId" value="">
                    <div class="mb-3">
                        <label class="form-label">Student Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="mobile" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch</label>
                        <select class="form-control" name="branch_id" id="studentBranch" required>
                            <option value="0">-- Select Branch --</option>
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= intval($b['id']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveStudent()">Save Student</button>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script>
    // Client-side search functionality with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchValue = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#students-table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        }, 200);
    });
    // Smooth fade-in effect for page content
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.dashboard-container').classList.add('show');
    });
    // Export to Excel
    function exportToExcel() {
        CRUD.showLoading('tableContainer');
        setTimeout(() => {
            window.location.href = '?page=students&export=excel';
            CRUD.hideLoading();
        }, 1000);
    }
    // Print table
    function printTable() {
        const table = document.getElementById('students-table').cloneNode(true);
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Students Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f8f9fa; }
                    </style>
                </head>
                <body>
                    <h2>Students Report</h2>
                    ${table.outerHTML}
                    <p>Generated on: ${new Date().toLocaleDateString()}</p>
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
    // Refresh table
    function refreshTable() {
        CRUD.showLoading('tableContainer');
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
    // Use shared CRUD loading helpers (CRUD.showLoading / CRUD.hideLoading)
    // Student management functions
    // Mode handling for modal (edit/view)
    function setModalMode(mode) {
        const form = document.getElementById('addStudentForm');
        const saveBtn = document.querySelector('#addStudentModal .btn-primary');
        if (mode === 'view') {
            Array.from(form.elements).forEach(el => el.disabled = true);
            saveBtn.style.display = 'none';
            document.querySelector('#addStudentModal .modal-title').innerText = 'View Student';
        } else {
            Array.from(form.elements).forEach(el => el.disabled = false);
            saveBtn.style.display = '';
            document.querySelector('#addStudentModal .modal-title').innerText = mode === 'edit' ? 'Edit Student' : 'Add New Student';
        }
    }

    async function editStudent(id) {
        CRUD.showLoading('tableContainer');
        try {
            const res = await fetch(`api/students.php?action=get&id=${encodeURIComponent(id)}`);
            const data = await res.json();
            if (data.success && data.data) {
                const s = data.data;
                document.getElementById('studentId').value = s.id || '';
                document.querySelector('#addStudentForm [name="name"]').value = s.name || '';
                document.querySelector('#addStudentForm [name="email"]').value = s.email || '';
                document.querySelector('#addStudentForm [name="mobile"]').value = s.mobile || s.phone || '';
                document.getElementById('studentBranch').value = s.branch_id ?? 0;
                setModalMode('edit');
                const modalEl = document.getElementById('addStudentModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                CRUD.toastError('Student not found');
            }
        } catch (e) {
            CRUD.toastError('Failed to load student: ' + e.message);
        } finally { CRUD.hideLoading(); }
    }

    async function viewStudent(id) {
        CRUD.showLoading('tableContainer');
        try {
            const res = await fetch(`api/students.php?action=get&id=${encodeURIComponent(id)}`);
            const data = await res.json();
            if (data.success && data.data) {
                const s = data.data;
                document.getElementById('studentId').value = s.id || '';
                document.querySelector('#addStudentForm [name="name"]').value = s.name || '';
                document.querySelector('#addStudentForm [name="email"]').value = s.email || '';
                document.querySelector('#addStudentForm [name="mobile"]').value = s.mobile || s.phone || '';
                document.getElementById('studentBranch').value = s.branch_id ?? 0;
                setModalMode('view');
                const modalEl = document.getElementById('addStudentModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                CRUD.toastError('Student not found');
            }
        } catch (e) {
            CRUD.toastError('Failed to load student: ' + e.message);
        } finally { CRUD.hideLoading(); }
    }

    async function deleteStudent(id) {
        if (!confirm('Are you sure you want to delete this student?')) return;
        CRUD.showLoading('tableContainer');
        try {
            const params = new URLSearchParams();
            params.append('id', id);
            const res = await fetch('api/students.php?action=delete', { method: 'POST', body: params });
            const data = await res.json();
            if (data.success) {
                CRUD.toastSuccess(data.message || 'Deleted');
                refreshTable();
            } else {
                CRUD.toastError('Delete failed: ' + (data.message || data.error || 'Unknown error'));
            }
        } catch (e) {
            CRUD.toastError('Delete request failed: ' + e.message);
        } finally { CRUD.hideLoading(); }
    }

    async function saveStudent() {
        const form = document.getElementById('addStudentForm');
        const formData = new FormData(form);
        // Ensure required fields for backend
        if (!formData.get('name')) { CRUD.toastError('Name is required'); return; }
        if (!formData.get('email')) { CRUD.toastError('Email is required'); return; }
        if (!formData.get('branch_id')) formData.set('branch_id', 0);
        const modalEl = document.getElementById('addStudentModal');
        CRUD.modalLoadingStart(modalEl);
        try {
            const id = formData.get('id');
            const action = id ? 'update' : 'create';
            const res = await fetch('api/students.php?action=' + action, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
                CRUD.toastSuccess(data.message || 'Saved');
                refreshTable();
            } else {
                CRUD.toastError('Save failed: ' + (data.message || data.error || 'Unknown error'));
            }
        } catch (e) {
            CRUD.toastError('Save request failed: ' + e.message);
        } finally { CRUD.modalLoadingStop(modalEl); }
    }
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            document.querySelector('[data-bs-target="#addStudentModal"]').click();
        }
    });
</script>

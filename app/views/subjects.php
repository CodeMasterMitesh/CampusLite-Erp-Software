<?php
// app/views/subjects.php
$subjects = [];
$controllerFile = __DIR__ . '/../controllers/SubjectController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $cls = 'SubjectController';
    if (class_exists($cls) && method_exists($cls, 'getAll')) {
        $subjects = $cls::getAll();
    }
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$total = count($subjects);
$totalPages = 1;
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-book"></i> Subjects</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
            <i class="fas fa-plus"></i> Add New Subject
        </button>
    </div>
    <div class="advanced-table-container">
        <div class="table-controls">
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search subjects..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-success btn-action" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    <button class="btn btn-secondary btn-action" onclick="printTable()"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-info btn-action" onclick="refreshTable()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
        </div>
        <div class="table-responsive position-relative" id="tableContainer">
            <table class="table data-table" id="subjects-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No subjects found</h4>
                                    <p>No subjects match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal"><i class="fas fa-plus"></i> Add First Subject</button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['title'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['description'] ?? '') ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-table" onclick="editSubject(<?= $s['id'] ?? 0 ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteSubject(<?= $s['id'] ?? 0 ?>)"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">Showing <?= count($subjects) ?> of <?= $total ?> subjects</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSubjectForm">
                    <div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" onclick="saveSubject()">Save Subject</button></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script>
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const v = e.target.value.toLowerCase();
            document.querySelectorAll('#subjects-table tbody tr').forEach(r => r.style.display = r.innerText.toLowerCase().includes(v) ? '' : 'none');
        }, 200);
    });
    document.addEventListener('DOMContentLoaded', () => document.querySelector('.dashboard-container').classList.add('show'));

    function exportToExcel() {
        showLoading();
        setTimeout(() => {
            window.location.href = '?page=subjects&export=excel';
            hideLoading();
        }, 800);
    }

    function printTable() {
        const table = document.getElementById('subjects-table').cloneNode(true);
        const w = window.open('', '_blank');
        w.document.write(`<html><head><title>Subjects</title><style>body{font-family:Arial}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}</style></head><body><h2>Subjects</h2>${table.outerHTML}<p>Generated on: ${new Date().toLocaleDateString()}</p></body></html>`);
        w.document.close();
        w.print();
    }

    function refreshTable() {
        showLoading();
        setTimeout(() => location.reload(), 600);
    }

    function showLoading() {
        const c = document.getElementById('tableContainer');
        const o = document.createElement('div');
        o.className = 'loading-overlay';
        o.innerHTML = '<div class="spinner-border text-primary spinner" role="status"><span class="visually-hidden">Loading...</span></div>';
        c.style.position = 'relative';
        c.appendChild(o);
    }

    function hideLoading() {
        const o = document.querySelector('.loading-overlay');
        if (o) o.remove();
    }

    function editSubject(id) {
        alert('Edit subject ' + id);
    }

    function deleteSubject(id) {
        if (confirm('Delete subject ' + id + '?')) {
            showLoading();
            setTimeout(() => {
                alert('Deleted ' + id);
                hideLoading();
                refreshTable();
            }, 900);
        }
    }

    function saveSubject() {
        alert('Saved');
        document.getElementById('addSubjectModal').querySelector('.btn-close')?.click();
        refreshTable();
    }
</script>
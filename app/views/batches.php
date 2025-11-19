<?php
// app/views/batches.php
$batches = [];
$controllerFile = __DIR__ . '/../controllers/BatchController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $cls = 'BatchController';
    if (class_exists($cls) && method_exists($cls, 'getAll')) {
        $batches = $cls::getAll();
    }
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$totalBatches = count($batches);
$totalPages = 1;
// load branches and courses
$branchFile = __DIR__ . '/../controllers/BranchController.php';
$branches = [];
if (file_exists($branchFile)) { require_once $branchFile; if (class_exists('BranchController') && method_exists('BranchController','getAll')) $branches = BranchController::getAll(); }
$courseFile = __DIR__ . '/../controllers/CourseController.php';
$courses = [];
if (file_exists($courseFile)) { require_once $courseFile; if (class_exists('CourseController') && method_exists('CourseController','getAll')) $courses = CourseController::getAll(); }
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-layer-group"></i> Batches</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addBatchModal">
            <i class="fas fa-plus"></i> Add New Batch
        </button>
    </div>
    <!-- Table Container -->
    <div class="advanced-table-container">
        <!-- Table Controls -->
        <div class="table-controls">
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search batches..." value="<?= htmlspecialchars($search) ?>">
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
            <table class="table data-table" id="batches-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No batches found</h4>
                                    <p>No batches match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                                        <i class="fas fa-plus"></i> Add First Batch
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <tr>
                                <td><?= htmlspecialchars($batch['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($batch['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($batch['course'] ?? '') ?></td>
                                <td><?= htmlspecialchars($batch['start_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($batch['end_date'] ?? '') ?></td>
                                <td>
                                    <?php if (isset($batch['status'])): ?>
                                        <span class="status-badge <?= $batch['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                            <?= ucfirst($batch['status']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-table" onclick="editBatch(<?= $batch['id'] ?? 0 ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewBatch(<?= $batch['id'] ?? 0 ?>)" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteBatch(<?= $batch['id'] ?? 0 ?>)" title="Delete">
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
                Showing <?= count($batches) ?> of <?= $totalBatches ?> batches
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=batches&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=batches&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=batches&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
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
<!-- Add Batch Modal -->
<div class="modal fade" id="addBatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBatchForm">
                    <input type="hidden" name="id" id="batchId" value="">
                    <div class="mb-3">
                        <label class="form-label">Batch Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select class="form-control" name="course_id" id="batchCourse" required>
                            <option value="0">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= intval($c['id']) ?>"><?= htmlspecialchars($c['title'] ?? $c['name'] ?? 'Course') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveBatch()">Save Batch</button>
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
            const rows = document.querySelectorAll('#batches-table tbody tr');
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
        showLoading();
        setTimeout(() => {
            window.location.href = '?page=batches&export=excel';
            hideLoading();
        }, 1000);
    }
    // Print table
    function printTable() {
        const table = document.getElementById('batches-table').cloneNode(true);
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Batches Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f8f9fa; }
                    </style>
                </head>
                <body>
                    <h2>Batches Report</h2>
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
        showLoading();
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
    // Loading states
    function showLoading() {
        const container = document.getElementById('tableContainer');
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="spinner-border text-primary spinner" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        container.style.position = 'relative';
        container.appendChild(overlay);
    }
    function hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) overlay.remove();
    }
    // Batch management functions
    async function editBatch(id){ CRUD.showLoading('tableContainer'); try{ const res=await CRUD.get(`api/batches.php?action=get&id=${encodeURIComponent(id)}`); if(res.success&&res.data){ const b=res.data; document.getElementById('batchId').value=b.id||''; document.querySelector('#addBatchForm [name="name"]').value=b.title||b.name||''; document.querySelector('#addBatchForm [name="start_date"]').value=b.start_date||''; document.querySelector('#addBatchForm [name="end_date"]').value=b.end_date||''; document.getElementById('batchCourse').value=b.course_id||0; const modalEl=document.getElementById('addBatchModal'); const modal=bootstrap.Modal.getOrCreateInstance(modalEl); modal.show(); } else alert('Batch not found'); }catch(e){ alert('Failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    async function viewBatch(id){ await editBatch(id); const form=document.getElementById('addBatchForm'); Array.from(form.elements).forEach(el=>el.disabled=true); const saveBtn=document.querySelector('#addBatchModal .btn-primary'); if(saveBtn) saveBtn.style.display='none'; document.querySelector('#addBatchModal .modal-title').innerText='View Batch'; }
    async function deleteBatch(id){ if(!confirm('Delete batch '+id+'?')) return; CRUD.showLoading('tableContainer'); try{ const p=new URLSearchParams(); p.append('id', id); const res=await CRUD.post('api/batches.php?action=delete', p); if(res.success) refreshTable(); else alert('Delete failed'); }catch(e){ alert('Delete failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    async function saveBatch(){ const form=document.getElementById('addBatchForm'); const params=new FormData(form); if(!params.get('name')){ alert('Name required'); return;} CRUD.showLoading('tableContainer'); try{ const res=await CRUD.post('api/batches.php?action=create', params); if(res.success){ const modalEl=document.getElementById('addBatchModal'); const modal=bootstrap.Modal.getOrCreateInstance(modalEl); modal.hide(); refreshTable(); } else alert('Save failed: '+(res.message||res.error||'Unknown')); }catch(e){ alert('Request failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            document.querySelector('[data-bs-target="#addBatchModal"]').click();
        }
    });
</script>

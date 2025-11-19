<?php
// app/views/branches.php
require_once __DIR__ . '/../controllers/BranchController.php';
$branches = BranchController::getAll();
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$totalBranches = count($branches);
$totalPages = 1;
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
        <!-- Breadcrumbs -->
        <div class="breadcrumb-container d-flex justify-content-between align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-code-branch"></i> Branches</li>
                </ol>
            </nav>
            <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                <i class="fas fa-plus"></i> Add New Branch
            </button>
        </div>
        <!-- Table Container -->
        <div class="advanced-table-container">
            <!-- Table Controls -->
            <div class="table-controls">
                <div class="table-header">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search branches..." value="<?= htmlspecialchars($search) ?>">
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
                <table class="table data-table" id="branches-table">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th width="100">Status</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (empty($branches)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h4>No branches found</h4>
                                        <p>No branches match your search criteria</p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                                            <i class="fas fa-plus"></i> Add First Branch
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branches as $branch): ?>
                                <tr>
                                    <td><?= htmlspecialchars($branch['id']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <i class="fas fa-code-branch text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                            <strong><?= htmlspecialchars($branch['name']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($branch['address']) ?></td>
                                    <td><?= htmlspecialchars($branch['phone']) ?></td>
                                    <td><?= htmlspecialchars($branch['email']) ?></td>
                                    <td>
                                        <?php if (isset($branch['status'])): ?>
                                            <span class="status-badge <?= $branch['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                                <?= ucfirst($branch['status']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-inactive">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="btn btn-sm btn-outline-primary btn-table" onclick="editBranch(<?= $branch['id'] ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info btn-table" onclick="viewBranch(<?= $branch['id'] ?>)" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteBranch(<?= $branch['id'] ?>)" title="Delete">
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
                    Showing <?= count($branches) ?> of <?= $totalBranches ?> branches
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=branches&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=branches&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=branches&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
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

    <!-- Add Branch Modal -->
    <div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBranchForm">
                        <input type="hidden" name="id" id="branchId" value="">
                        <div class="mb-3">
                            <label class="form-label">Branch Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveBranch()">Save Branch</button>
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
                const rows = document.querySelectorAll('#branches-table tbody tr');
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
                window.location.href = '?page=branches&export=excel';
                CRUD.hideLoading();
            }, 1000);
        }

        // Print table
        function printTable() {
            const table = document.getElementById('branches-table').cloneNode(true);
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Branches Report</title>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            table { width: 100%; border-collapse: collapse; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f8f9fa; }
                        </style>
                    </head>
                    <body>
                        <h2>Branches Report</h2>
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

        // Use shared CRUD.showLoading / CRUD.hideLoading

        // Branch management functions
        async function saveBranch() {
            const form = document.getElementById('addBranchForm');
            const params = new FormData(form);
            if (!params.get('name')) { CRUD.toastError('Name is required'); return; }
            const modalEl = document.getElementById('addBranchModal');
            CRUD.modalLoadingStart(modalEl);
            try {
                const data = await CRUD.post('api/branches.php?action=create', params);
                if (data.success) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();
                    CRUD.toastSuccess(data.message || 'Saved');
                    refreshTable();
                } else {
                    CRUD.toastError('Save failed: ' + (data.message || data.error || 'Unknown'));
                }
            } catch (e) {
                CRUD.toastError('Request failed: ' + e.message);
            } finally { CRUD.modalLoadingStop(modalEl); }
        }

        async function deleteBranch(id) {
            if (!confirm('Are you sure you want to delete this branch?')) return;
            CRUD.showLoading('tableContainer');
            try {
                const params = new URLSearchParams(); params.append('id', id);
                const data = await CRUD.post('api/branches.php?action=delete', params);
                if (data.success){ CRUD.toastSuccess(data.message || 'Deleted'); refreshTable(); } else CRUD.toastError('Delete failed: ' + (data.message || data.error || 'Unknown'));
            } catch (e) { CRUD.toastError('Delete request failed: ' + e.message); }
            finally { CRUD.hideLoading(); }
        }

        function editBranch(id) { CRUD.toastError('Edit not implemented for branches yet.'); }
        function viewBranch(id) { CRUD.toastError('View not implemented for branches yet.'); }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                document.querySelector('[data-bs-target="#addBranchModal"]').click();
            }
        });
    </script>

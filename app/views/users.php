<?php
// app/views/users.php
$users = [];
$controllerFile = __DIR__ . '/../controllers/UserController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $cls = 'UserController';
    if (class_exists($cls) && method_exists($cls, 'getAll')) {
        $users = $cls::getAll();
    }
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$total = count($users);
$totalPages = 1;
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-users"></i> Users</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Add New User</button>
    </div>
    <div class="advanced-table-container">
        <div class="table-controls">
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-success btn-action" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    <button class="btn btn-secondary btn-action" onclick="printTable()"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-info btn-action" onclick="refreshTable()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
        </div>
        <div class="table-responsive position-relative" id="tableContainer">
            <table class="table data-table" id="users-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No users found</h4>
                                    <p>No users match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-plus"></i> Add First User</button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['role'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['branch_id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['mobile'] ?? '') ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-table" onclick="editUser(<?= $u['id'] ?? 0 ?>)"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewUser(<?= $u['id'] ?? 0 ?>)"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteUser(<?= $u['id'] ?? 0 ?>)"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add New User</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Mobile</label><input class="form-control" name="mobile"></div>
                    <div class="mb-3"><label class="form-label">Role</label><select class="form-control" name="role"><option>admin</option><option>staff</option></select></div>
                    <div class="mb-3"><label class="form-label">Branch</label><input class="form-control" name="branch_id"></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" onclick="saveUser()">Save User</button></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script>
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e){ clearTimeout(searchTimeout); searchTimeout=setTimeout(()=>{ const v=e.target.value.toLowerCase(); document.querySelectorAll('#users-table tbody tr').forEach(r=>r.style.display=r.innerText.toLowerCase().includes(v)?'':'none'); },200);} );
    document.addEventListener('DOMContentLoaded', ()=>document.querySelector('.dashboard-container').classList.add('show'));
    function exportToExcel(){ showLoading(); setTimeout(()=>{ window.location.href='?page=users&export=excel'; hideLoading(); },800);} function printTable(){ const table=document.getElementById('users-table').cloneNode(true); const w=window.open('','_blank'); w.document.write(`<html><head><title>Users</title></head><body><h2>Users</h2>${table.outerHTML}</body></html>`); w.document.close(); w.print(); }
    function refreshTable(){ showLoading(); setTimeout(()=>location.reload(),600);} function showLoading(){ const c=document.getElementById('tableContainer'); const o=document.createElement('div'); o.className='loading-overlay'; o.innerHTML='<div class="spinner-border text-primary spinner" role="status"><span class="visually-hidden">Loading...</span></div>'; c.style.position='relative'; c.appendChild(o);} function hideLoading(){ const o=document.querySelector('.loading-overlay'); if(o) o.remove(); }
    function editUser(id){ alert('Edit user '+id);} function viewUser(id){ alert('View user '+id);} function deleteUser(id){ if(confirm('Delete user '+id+'?')){ showLoading(); setTimeout(()=>{ alert('Deleted'); hideLoading(); refreshTable(); },900);} }
    function saveUser(){ alert('Saved'); document.getElementById('addUserModal').querySelector('.btn-close')?.click(); refreshTable(); }
</script>

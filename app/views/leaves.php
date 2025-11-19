<?php
// app/views/leaves.php
$leaves = [];
$controllerFile = __DIR__ . '/../controllers/LeaveController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $cls = 'LeaveController';
    if (class_exists($cls) && method_exists($cls, 'getAll')) {
        $leaves = $cls::getAll();
    }
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$total = count($leaves);
$totalPages = 1;
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-calendar-alt"></i> Leaves</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#addLeaveModal"><i class="fas fa-plus"></i> Apply Leave</button>
    </div>
    <div class="advanced-table-container">
        <div class="table-controls">
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search leaves..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-success btn-action" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    <button class="btn btn-secondary btn-action" onclick="printTable()"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-info btn-action" onclick="refreshTable()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
        </div>
        <div class="table-responsive position-relative" id="tableContainer">
            <table class="table data-table" id="leaves-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Employee</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($leaves)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No leave records</h4>
                                    <p>No leaves match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal"><i class="fas fa-plus"></i> Apply Leave</button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaves as $l): ?>
                            <tr>
                                <td><?= htmlspecialchars($l['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['employee'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['from_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['to_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['type'] ?? '') ?></td>
                                <td><span class="status-badge <?= ($l['status'] ?? '') === 'approved' ? 'status-active' : 'status-inactive' ?>"><?= htmlspecialchars($l['status'] ?? 'pending') ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewLeave(<?= $l['id'] ?? 0 ?>)"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteLeave(<?= $l['id'] ?? 0 ?>)"><i class="fas fa-trash"></i></button>
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

<!-- Add Leave Modal -->
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Apply Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <form id="addLeaveForm">
                    <input type="hidden" name="id" id="leaveId" value="">
                    <input type="hidden" name="branch_id" id="leaveBranchId" value="0">
                    <div class="mb-3"><label class="form-label">Employee</label><input class="form-control" name="employee" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">From</label><input type="date" class="form-control" name="from_date" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">To</label><input type="date" class="form-control" name="to_date" required></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Type</label><select class="form-control" name="type"><option>Casual</option><option>Sick</option><option>Other</option></select></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" onclick="saveLeave()">Apply</button></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script>
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e){ clearTimeout(searchTimeout); searchTimeout=setTimeout(()=>{ const v=e.target.value.toLowerCase(); document.querySelectorAll('#leaves-table tbody tr').forEach(r=>r.style.display=r.innerText.toLowerCase().includes(v)?'':'none'); },200);} );
    document.addEventListener('DOMContentLoaded', ()=>document.querySelector('.dashboard-container').classList.add('show'));
    function exportToExcel(){ showLoading(); setTimeout(()=>{ window.location.href='?page=leaves&export=excel'; hideLoading(); },800);} function printTable(){ const table=document.getElementById('leaves-table').cloneNode(true); const w=window.open('','_blank'); w.document.write(`<html><head><title>Leaves</title></head><body><h2>Leaves</h2>${table.outerHTML}</body></html>`); w.document.close(); w.print(); }
    function refreshTable(){ showLoading(); setTimeout(()=>location.reload(),600);} function showLoading(){ const c=document.getElementById('tableContainer'); const o=document.createElement('div'); o.className='loading-overlay'; o.innerHTML='<div class="spinner-border text-primary spinner" role="status"><span class="visually-hidden">Loading...</span></div>'; c.style.position='relative'; c.appendChild(o);} function hideLoading(){ const o=document.querySelector('.loading-overlay'); if(o) o.remove(); }
    async function viewLeave(id){ CRUD.showLoading('tableContainer'); try{ const res=await CRUD.get(`api/leaves.php?action=get&id=${encodeURIComponent(id)}`); if(res.success&&res.data){ const l=res.data; document.getElementById('leaveId').value=l.id||''; document.querySelector('#addLeaveForm [name="employee"]').value=l.employee||''; document.querySelector('#addLeaveForm [name="from_date"]').value=l.from_date||''; document.querySelector('#addLeaveForm [name="to_date"]').value=l.to_date||''; document.querySelector('#addLeaveForm [name="type"]').value=l.leave_type||''; document.getElementById('leaveBranchId').value=l.branch_id??0; const modalEl=document.getElementById('addLeaveModal'); const modal=bootstrap.Modal.getOrCreateInstance(modalEl); modal.show(); } else alert('Not found'); }catch(e){ alert('Failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    async function deleteLeave(id){ if(!confirm('Delete leave '+id+'?')) return; CRUD.showLoading('tableContainer'); try{ const p=new URLSearchParams(); p.append('id', id); const res=await CRUD.post('api/leaves.php?action=delete', p); if(res.success) refreshTable(); else alert('Delete failed'); }catch(e){ alert('Delete failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    async function saveLeave(){ const form=document.getElementById('addLeaveForm'); const params=new FormData(form); if(!params.get('employee')){ alert('Employee required'); return;} CRUD.showLoading('tableContainer'); try{ const res=await CRUD.post('api/leaves.php?action=apply', params); if(res.success){ const modalEl=document.getElementById('addLeaveModal'); const modal=bootstrap.Modal.getOrCreateInstance(modalEl); modal.hide(); refreshTable(); } else alert('Save failed'); }catch(e){ alert('Request failed: '+e.message);} finally{ CRUD.hideLoading(); } }
</script>

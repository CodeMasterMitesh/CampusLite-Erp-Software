<?php
// app/views/salaries.php
$salaries = [];
$controllerFile = __DIR__ . '/../controllers/SalaryController.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $cls = 'SalaryController';
    if (class_exists($cls) && method_exists($cls, 'getAll')) {
        $salaries = $cls::getAll();
    }
}
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$total = count($salaries);
$totalPages = 1;
?>
<?php include __DIR__ . '/partials/nav.php'; ?>
<div class="container-fluid dashboard-container fade-in">
    <div class="breadcrumb-container d-flex justify-content-between align-items-center">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-money-check-alt"></i> Salaries</li>
            </ol>
        </nav>
        <button class="btn btn-primary btn-action" data-bs-toggle="modal" data-bs-target="#generateSalaryModal"><i class="fas fa-plus"></i> Generate Salary</button>
    </div>
    <div class="advanced-table-container">
        <div class="table-controls">
            <div class="table-header">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search salaries..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-success btn-action" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    <button class="btn btn-secondary btn-action" onclick="printTable()"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-info btn-action" onclick="refreshTable()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
        </div>
        <div class="table-responsive position-relative" id="tableContainer">
            <table class="table data-table" id="salaries-table">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th>Employee</th>
                        <th>Month</th>
                        <th>Gross</th>
                        <th>Net</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($salaries)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>No salary records</h4>
                                    <p>No salaries match your search criteria</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateSalaryModal"><i class="fas fa-plus"></i> Generate Salary</button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($salaries as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['employee'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['month'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['gross'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['net'] ?? '') ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn btn-sm btn-outline-info btn-table" onclick="viewSalary(<?= $s['id'] ?? 0 ?>)"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-outline-danger btn-table" onclick="deleteSalary(<?= $s['id'] ?? 0 ?>)"><i class="fas fa-trash"></i></button>
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

<!-- Generate Salary Modal -->
<div class="modal fade" id="generateSalaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Generate Salary</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <form id="generateSalaryForm">
                    <input type="hidden" name="id" id="salaryId" value="">
                    <div class="mb-3"><label class="form-label">Month</label><input type="month" class="form-control" name="month" required></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" onclick="generateSalary()">Generate</button></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
<script>
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e){ clearTimeout(searchTimeout); searchTimeout=setTimeout(()=>{ const v=e.target.value.toLowerCase(); document.querySelectorAll('#salaries-table tbody tr').forEach(r=>r.style.display=r.innerText.toLowerCase().includes(v)?'':'none'); },200);} );
    document.addEventListener('DOMContentLoaded', ()=>document.querySelector('.dashboard-container').classList.add('show'));
    function exportToExcel(){ showLoading(); setTimeout(()=>{ window.location.href='?page=salaries&export=excel'; hideLoading(); },800);} function printTable(){ const table=document.getElementById('salaries-table').cloneNode(true); const w=window.open('','_blank'); w.document.write(`<html><head><title>Salaries</title></head><body><h2>Salaries</h2>${table.outerHTML}</body></html>`); w.document.close(); w.print(); }
    function refreshTable(){ showLoading(); setTimeout(()=>location.reload(),600);} function showLoading(){ const c=document.getElementById('tableContainer'); const o=document.createElement('div'); o.className='loading-overlay'; o.innerHTML='<div class="spinner-border text-primary spinner" role="status"><span class="visually-hidden">Loading...</span></div>'; c.style.position='relative'; c.appendChild(o);} function hideLoading(){ const o=document.querySelector('.loading-overlay'); if(o) o.remove(); }
    async function viewSalary(id){ CRUD.showLoading('tableContainer'); try{ const res=await CRUD.get(`api/salary.php?action=get&id=${encodeURIComponent(id)}`); if(res.success && res.data){ const s=res.data; document.getElementById('salaryId').value = s.id || ''; document.querySelector('#generateSalaryForm [name="month"]').value = s.salary_month || s.month || ''; const modalEl=document.getElementById('generateSalaryModal'); const modal=bootstrap.Modal.getOrCreateInstance(modalEl); modal.show(); } else alert('Not found'); }catch(e){ alert('Failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    async function deleteSalary(id){ if(!confirm('Delete salary '+id+'?')) return; CRUD.showLoading('tableContainer'); try{ const p=new URLSearchParams(); p.append('id', id); const res = await CRUD.post('api/salary.php?action=delete', p); if(res.success) refreshTable(); else alert('Delete failed'); }catch(e){ alert('Delete failed: '+e.message);} finally{ CRUD.hideLoading(); } }
    async function generateSalary(){ const form=document.getElementById('generateSalaryForm'); const params=new FormData(form); if(!params.get('month')){ alert('Month required'); return;} CRUD.showLoading('tableContainer'); try{ const res = await CRUD.post('api/salary.php?action=generate', params); if(res.success){ const modalEl=document.getElementById('generateSalaryModal'); const modal=bootstrap.Modal.getOrCreateInstance(modalEl); modal.hide(); refreshTable(); } else alert('Generate failed'); }catch(e){ alert('Request failed: '+e.message);} finally{ CRUD.hideLoading(); } }
</script>

// subjects.js - centralised page logic for subjects.php
function initSubjects() {
    // init DataTable with filters
    try { initAdvancedTable('#subjects-table'); } catch(e) { console.error('initSubjects: initAdvancedTable failed', e); }
    // page fade-in
    const container = document.querySelector('.dashboard-container'); if (container) container.classList.add('show');
    // Selection handling for delete-selected
    const selectAll = document.getElementById('select-all-subjects');
    const headerDeleteBtn = document.getElementById('delete-selected-subjects-header');
    function updateSelectionUI() {
        const any = !!document.querySelectorAll('#subjects-table tbody .row-select:checked').length;
        if (headerDeleteBtn) headerDeleteBtn.style.display = any ? '' : 'none';
        if (selectAll) {
            const total = document.querySelectorAll('#subjects-table tbody .row-select').length;
            const checked = document.querySelectorAll('#subjects-table tbody .row-select:checked').length;
            selectAll.checked = total>0 && checked === total;
        }
    }
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checked = !!this.checked;
            document.querySelectorAll('#subjects-table tbody .row-select').forEach(cb => cb.checked = checked);
            updateSelectionUI();
        });
    }
    document.addEventListener('change', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('row-select')) updateSelectionUI();
    });
    // initialize hidden state
    updateSelectionUI();
}

window.initSubjects = initSubjects;
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSubjects); else try { initSubjects(); } catch(e) { console.error('initSubjects immediate failed', e); }

// Client-side search (if there's a search input with id=searchInput)
(function(){
    const si = document.getElementById('searchInput');
    if (!si) return;
    let searchTimeout;
    si.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const v = e.target.value.toLowerCase();
            document.querySelectorAll('#subjects-table tbody tr').forEach(r => r.style.display = r.innerText.toLowerCase().includes(v) ? '' : 'none');
        }, 200);
    });
})();

function exportToExcel() {
    if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
    setTimeout(() => {
        window.location.href = '?page=subjects&export=excel';
        if (window.CRUD && CRUD.hideLoading) CRUD.hideLoading();
    }, 800);
}

function printTable() {
    const table = document.getElementById('subjects-table').cloneNode(true);
    const w = window.open('', '_blank');
    w.document.write(`<html><head><title>Subjects</title><style>body{font-family:Arial}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}</style></head><body><h2>Subjects</h2>${table.outerHTML}<p>Generated on: ${new Date().toLocaleDateString()}</p></body></html>`);
    w.document.close();
    w.print();
}

function refreshTable() { if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer'); setTimeout(() => location.reload(), 600); }

async function editSubject(id) {
    if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
    try {
        const res = await CRUD.get(`api/subjects.php?action=get&id=${encodeURIComponent(id)}`);
        if (res.success && res.data) {
            renderSubjectForm(res.data, 'edit');
        } else {
            CRUD.toastError('Subject not found');
        }
    } catch (e) { CRUD.toastError('Failed to load: ' + e.message); }
    finally { window.CRUD && CRUD.hideLoading && CRUD.hideLoading(); }
}

async function viewSubject(id) {
    if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
    try {
        const res = await CRUD.get(`api/subjects.php?action=get&id=${encodeURIComponent(id)}`);
        if (res.success && res.data) {
            renderSubjectForm(res.data, 'view');
        } else {
            CRUD.toastError('Subject not found');
        }
    } catch (e) { CRUD.toastError('Failed to load: ' + e.message); }
    finally { window.CRUD && CRUD.hideLoading && CRUD.hideLoading(); }
}

function renderSubjectForm(s, mode = 'edit') {
    // Populate fields
    document.getElementById('subjectId').value = s.id || '';
    document.getElementById('subjectIdDisplay').textContent = s.id || '-';
    document.getElementById('subjectTitle').value = s.title || '';
    document.getElementById('subjectDescription').value = s.description || '';
    
    // Disable form if viewing
    const form = document.getElementById('addSubjectForm');
    if (form) {
        Array.from(form.elements).forEach(el => {
            if (el.id !== 'subjectId') {
                el.disabled = (mode === 'view');
            }
        });
    }
    
    // Update modal title and button
    const title = document.getElementById('subjectModalTitle');
    if (title) title.textContent = mode === 'view' ? 'View Subject' : 'Edit Subject';
    
    const saveBtn = document.getElementById('subjectSaveBtn');
    if (saveBtn) {
        saveBtn.style.display = mode === 'view' ? 'none' : '';
        saveBtn.textContent = mode === 'view' ? '' : 'Update Subject';
    }
    
    // Show modal
    bootstrap.Modal.getOrCreateInstance(document.getElementById('addSubjectModal')).show();
}

async function deleteSubject(id) {
    if (!confirm('Delete subject ' + id + '?')) return;
    if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
    try {
        const params = new URLSearchParams(); params.append('id', id);
        const res = await CRUD.post('api/subjects.php?action=delete', params);
        if (res.success) { CRUD.toastSuccess(res.message || 'Deleted'); refreshTable(); } else CRUD.toastError('Delete failed');
    } catch (e) { alert('Delete failed: ' + e.message); }
    finally { window.CRUD && CRUD.hideLoading && CRUD.hideLoading(); }
}

// Delete selected subjects (bulk)
async function deleteSelectedSubjects() {
    const checked = Array.from(document.querySelectorAll('#subjects-table tbody .row-select:checked')).map(cb => cb.dataset.id).filter(Boolean);
    if (!checked.length) { CRUD.toastError('No subjects selected'); return; }
    if (!confirm(`Delete ${checked.length} selected subject(s)?`)) return;
    if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
    try {
        for (const id of checked) {
            const params = new URLSearchParams(); params.append('id', id);
            const res = await CRUD.post('api/subjects.php?action=delete', params);
            if (!res.success) console.error('Failed to delete subject', id, res);
        }
        CRUD.toastSuccess('Deleted selected subjects');
        refreshTable();
    } catch (e) {
        CRUD.toastError('Bulk delete failed: ' + e.message);
    } finally {
        window.CRUD && CRUD.hideLoading && CRUD.hideLoading();
    }
}

async function saveSubject() {
    const form = document.getElementById('addSubjectForm');
    const params = new FormData(form);
    if (!params.get('title')) { CRUD.toastError('Title required'); return; }
    const modalEl = document.getElementById('addSubjectModal');
    if (window.CRUD && CRUD.modalLoadingStart) CRUD.modalLoadingStart(modalEl);
    try {
        const id = params.get('id');
        const action = id ? 'update' : 'create';
        const res = await CRUD.post('api/subjects.php?action=' + action, params);
        if (res.success) {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            CRUD.toastSuccess(res.message || 'Saved');
            refreshTable();
        } else {
            CRUD.toastError('Save failed: ' + (res.message || res.error || 'Unknown'));
        }
    } catch (e) { alert('Request failed: ' + e.message); }
    finally { window.CRUD && CRUD.modalLoadingStop && CRUD.modalLoadingStop(modalEl); }
}

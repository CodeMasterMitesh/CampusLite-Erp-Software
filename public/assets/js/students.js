// students.js - extracted from students.php
window.STUDENT_UPLOAD_BASE = window.STUDENT_UPLOAD_BASE || '/public/uploads/students/';

function resetStudentFormForAdd() {
    const form = document.getElementById('addStudentForm'); if (form) form.reset();
    const idEl = document.getElementById('studentId'); if (idEl) idEl.value = '';
    clearStudentPhotoPreview();
    const branchSel = document.getElementById('studentBranch'); if (branchSel) branchSel.value = branchSel.querySelector('option') ? branchSel.querySelector('option').value : '0';
    const statusSel = document.querySelector('#addStudentForm [name="status"]'); if (statusSel) statusSel.value = 'active';
    const coursesDiv = document.getElementById('courses-dynamic');
    if (coursesDiv) {
        while (coursesDiv.children.length > 1) coursesDiv.removeChild(coursesDiv.lastChild);
        const firstSelect = coursesDiv.querySelector('select[name="courses[]"]');
        if (firstSelect) firstSelect.selectedIndex = 0;
    }
    setModalMode('add');
}
function initStudents() {
    // Initialize DataTable with column filters
    try { initAdvancedTable('#students-table'); } catch(e) { console.error('initStudents: initAdvancedTable failed', e); }

    // page fade-in
    const container = document.querySelector('.dashboard-container');
    if (container) container.classList.add('show');

    // Prevent table-row hover effects from shifting layout
    try {
        const style = document.createElement('style');
        style.innerHTML = `
            #students-table tbody tr { transition: none !important; }
            #students-table tbody tr:hover { transform: none !important; box-shadow: none !important; margin: 0 !important; }
            #students-table thead tr.filters input { width: 100%; }
        `;
        document.head.appendChild(style);
    } catch (e) { console.error('initStudents: failed to inject style', e); }

    // Add More Course Dropdown functionality
    const addCourseBtn = document.getElementById('addCourseDropdown');
    if (addCourseBtn) {
        addCourseBtn.addEventListener('click', function() {
            const coursesDiv = document.getElementById('courses-dynamic');
            if (!coursesDiv) return;
            const firstRow = coursesDiv.querySelector('.course-row');
            if (!firstRow) return;
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('select') && (newRow.querySelector('select').selectedIndex = 0);
            coursesDiv.appendChild(newRow);
        });
    }

    // Delete course row functionality (delegated)
    const coursesDiv = document.getElementById('courses-dynamic');
    if (coursesDiv) {
        coursesDiv.addEventListener('click', function(e) {
            if (e.target.closest('.delete-course-btn')) {
                const row = e.target.closest('.course-row');
                if (this.querySelectorAll('.course-row').length > 1) {
                    row.remove();
                }
            }
        });
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            let input = document.querySelector('#students-table thead tr.filters input');
            if (input) { input.focus(); input.select && input.select(); }
            else setTimeout(() => {
                const delayed = document.querySelector('#students-table thead tr.filters input');
                if (delayed) { delayed.focus(); delayed.select && delayed.select(); }
            }, 200);
        }
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            if (typeof showAddModal === 'function') {
                showAddModal('addStudentModal', 'addStudentForm');
            } else {
                const btn = document.querySelector('[data-bs-target="#addStudentModal"]');
                btn && btn.click();
            }
        }
    });

    const addBtn = document.querySelector('[data-bs-target="#addStudentModal"], [data-modal-target="addStudentModal"]');
    if (addBtn && !addBtn.dataset.studentPrepared) {
        addBtn.dataset.studentPrepared = '1';
        addBtn.addEventListener('click', () => { resetStudentFormForAdd(); });
    }

    // Selection handling for delete-selected header button
    const selectAllStudents = document.getElementById('select-all-students');
    const headerDeleteBtn = document.getElementById('delete-selected-students-header');
    function updateSelectionUI() {
        const any = !!document.querySelectorAll('#students-table tbody .row-select:checked').length;
        if (headerDeleteBtn) {
            headerDeleteBtn.style.display = any ? '' : 'none';
            headerDeleteBtn.disabled = false;
        }
        if (selectAllStudents) {
            const total = document.querySelectorAll('#students-table tbody .row-select').length;
            const checked = document.querySelectorAll('#students-table tbody .row-select:checked').length;
            selectAllStudents.checked = total > 0 && checked === total;
        }
    }
    if (selectAllStudents) {
        selectAllStudents.addEventListener('change', function() {
            const checked = !!this.checked;
            document.querySelectorAll('#students-table tbody .row-select').forEach(cb => cb.checked = checked);
            updateSelectionUI();
        });
    }
    document.addEventListener('change', function(e){
        if (e.target && e.target.classList && e.target.classList.contains('row-select')) updateSelectionUI();
    });
    // initialize state (button visible, disabled if nothing selected)
    updateSelectionUI();

    // Ensure modal selects (branches / courses) are populated when modal opens
    async function populateStudentModalOptions(forceReload = false) {
        try {
            if (!forceReload && window._cachedBranches && window._cachedCourses) return;
            const [branchesRes, coursesRes] = await Promise.all([
                fetchJson('api/branches.php?action=list'),
                fetchJson('api/courses.php?action=list')
            ]);
            const branches = (branchesRes && branchesRes.success && Array.isArray(branchesRes.data)) ? branchesRes.data : [];
            const courses = (coursesRes && coursesRes.success && Array.isArray(coursesRes.data)) ? coursesRes.data : [];
            window._cachedBranches = branches;
            window._cachedCourses = courses;

            // Populate branch select
            const branchSel = document.getElementById('studentBranch');
            if (branchSel) {
                const cur = branchSel.value;
                branchSel.innerHTML = '<option value="0">-- Select Branch --</option>' + branches.map(b => `<option value="${b.id}">${escapeHtml(b.name || b.title || b.branch_name || b.label || '')}</option>`).join('');
                if (cur) branchSel.value = cur;
            }

            // Populate course selects template(s)
            const coursesDiv = document.getElementById('courses-dynamic');
            if (coursesDiv) {
                // For each select[name="courses[]"] update options
                const selects = coursesDiv.querySelectorAll('select[name="courses[]"]');
                selects.forEach(sel => {
                    const prev = sel.value;
                    sel.innerHTML = '<option value="">-- Select Course --</option>' + courses.map(c => `<option value="${c.id}">${escapeHtml(c.title || c.name || c.course_name || '')}</option>`).join('');
                    if (prev) sel.value = prev;
                });
            }
        } catch (err) {
            console.error('populateStudentModalOptions failed', err);
        }
    }

    // Utility escape helper to avoid HTML injection in option text
    function escapeHtml(str) {
        return String(str || '').replace(/[&<>\"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
    }

    // Expose and call populate function so other modules can refresh lists
    window.populateStudentModalOptions = populateStudentModalOptions;

    // Populate once now (so template rows have options)
    try { window.populateStudentModalOptions(); } catch(e){/* ignore */}

    // Also populate on modal show in case cache needs refreshing
    const studentModalEl = document.getElementById('addStudentModal');
    if (studentModalEl) {
        studentModalEl.addEventListener('show.bs.modal', function(e){
            window.populateStudentModalOptions(false);
            const trigger = e.relatedTarget;
            const isAddTrigger = !!(trigger && (trigger.matches('[data-bs-target="#addStudentModal"]') || trigger.matches('[data-modal-target="addStudentModal"]')));
            const idVal = document.getElementById('studentId')?.value || '';
            if (isAddTrigger || !idVal) {
                resetStudentFormForAdd();
            }
        });
    }

    // Listen for global refresh events
    window.addEventListener('globalListsRefreshed', function() {
        try { window.populateStudentModalOptions(true); } catch(e) { console.error('globalListsRefreshed handler failed', e); }
    });
}

// Expose initializer for AJAX loader and call appropriately
window.initStudents = initStudents;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStudents);
} else {
    // Document already ready — initialize immediately
    try { initStudents(); } catch(e) { console.error('initStudents immediate call failed', e); }
}

// Export to Excel
function exportToExcel() {
    if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer');
    setTimeout(() => {
        window.location.href = '?page=students&export=excel';
        if (window.CRUD && typeof CRUD.hideLoading === 'function') CRUD.hideLoading();
    }, 1000);
}

// Delete selected students (bulk)
async function deleteSelectedStudents() {
    const checked = Array.from(document.querySelectorAll('#students-table tbody .row-select:checked')).map(cb => cb.dataset.id).filter(Boolean);
    if (!checked.length) {
        window.CRUD && CRUD.toastError && CRUD.toastError('No students selected');
        return;
    }
    if (!confirm(`Delete ${checked.length} selected student(s)?`)) return;
    if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer');
    try {
        for (const id of checked) {
            const params = new URLSearchParams(); params.append('id', id);
            const res = await fetch('api/students.php?action=delete', { method: 'POST', body: params });
            const data = await res.json();
            if (!data.success) console.error('Failed to delete student', id, data);
        }
        window.CRUD && window.CRUD.toastSuccess && window.CRUD.toastSuccess('Deleted selected students');
        refreshTable();
    } catch (e) {
        window.CRUD && CRUD.toastError && CRUD.toastError('Bulk delete failed: ' + e.message);
    } finally {
        window.CRUD && typeof CRUD.hideLoading === 'function' && CRUD.hideLoading();
    }
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

function refreshTable() { if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer'); setTimeout(() => window.location.reload(), 800); }

// Modal mode helper
function setModalMode(mode) {
    const form = document.getElementById('addStudentForm');
    const saveBtn = document.querySelector('#addStudentModal .btn-primary');
    if (!form) return;
    if (mode === 'view') {
        Array.from(form.elements).forEach(el => el.disabled = true);
        saveBtn && (saveBtn.style.display = 'none');
        document.querySelector('#addStudentModal .modal-title') && (document.querySelector('#addStudentModal .modal-title').innerText = 'View Student');
        const removeBtn = document.getElementById('removeStudentPhoto'); if (removeBtn) removeBtn.style.display = 'none';
        const fileInput = document.getElementById('studentPhotoInput'); if (fileInput) fileInput.disabled = true;
    } else {
        Array.from(form.elements).forEach(el => el.disabled = false);
        if (saveBtn) {
            saveBtn.style.display = '';
            saveBtn.textContent = (mode === 'edit') ? 'Update Student' : 'Save Student';
        }
        const titleEl = document.querySelector('#addStudentModal .modal-title');
        if (titleEl) titleEl.innerText = (mode === 'edit') ? 'Edit Student' : 'Add New Student';
        const fileInput = document.getElementById('studentPhotoInput'); if (fileInput) fileInput.disabled = false;
    }
}

function clearStudentPhotoPreview() {
    const img = document.getElementById('studentPhotoPreview');
    if (img) { img.src = ''; img.style.display = 'none'; }
    const removeBtn = document.getElementById('removeStudentPhoto');
    if (removeBtn) removeBtn.style.display = 'none';
    const input = document.getElementById('studentPhotoInput');
    if (input) input.value = '';
}

// Fetch and populate student for edit
async function editStudent(id) {
    if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer');
    try {
        const form = document.getElementById('addStudentForm');
        const modalEl = document.getElementById('addStudentModal');
        if (!form || !modalEl) { window.CRUD && CRUD.toastError && CRUD.toastError('Student form not available'); return; }
        // ensure dropdowns are populated before setting values
        if (typeof window.populateStudentModalOptions === 'function') {
            await window.populateStudentModalOptions(true);
        }
        resetStudentFormForAdd();
        const data = await fetchJson(`api/students.php?action=get&id=${encodeURIComponent(id)}`);
        if (data.success && data.data) {
            const s = data.data;
            const setVal = (sel, val) => { const el = document.querySelector(sel); if (el) el.value = val ?? ''; };
            const setDate = (sel, val) => { const el = document.querySelector(sel); if (el) el.value = (val || '').slice(0, 10); };
            const setBranch = (val) => { const b = document.getElementById('studentBranch'); if (b) b.value = val ?? 0; };

            document.getElementById('studentId') && (document.getElementById('studentId').value = s.id || '');
            setVal('#addStudentForm [name="name"]', s.name);
            setVal('#addStudentForm [name="email"]', s.email);
            setVal('#addStudentForm [name="mobile"]', s.mobile || s.phone);
            setDate('#addStudentForm [name="dob"]', s.dob);
            setVal('#addStudentForm [name="education"]', s.education);
            setVal('#addStudentForm [name="college_name"]', s.college_name);
            setVal('#addStudentForm [name="father_name"]', s.father_name);
            setVal('#addStudentForm [name="address"]', s.address);
            setVal('#addStudentForm [name="pincode"]', s.pincode);
            setVal('#addStudentForm [name="state"]', s.state);
            setVal('#addStudentForm [name="city"]', s.city);
            setVal('#addStudentForm [name="area"]', s.area);
            setBranch(s.branch_id);
            const statusSel = document.querySelector('#addStudentForm [name="status"]'); if (statusSel) statusSel.value = (s.status == 1 || s.status === 'active') ? 'active' : 'inactive';

            const img = document.getElementById('studentPhotoPreview');
            const removeBtn = document.getElementById('removeStudentPhoto');
            if (img) {
                if (s.profile_photo) { img.src = STUDENT_UPLOAD_BASE + s.profile_photo; img.style.display = ''; if (removeBtn) removeBtn.style.display = ''; }
                else { clearStudentPhotoPreview(); }
            }

            // Courses: fetch selected courses for student (AJAX)
            const coursesDiv = document.getElementById('courses-dynamic');
            if (coursesDiv) {
                while (coursesDiv.children.length > 1) coursesDiv.removeChild(coursesDiv.lastChild);
                try {
                    const courseData = await fetchJson(`api/students.php?action=get_courses&id=${encodeURIComponent(s.id)}`);
                    if (courseData.success && Array.isArray(courseData.data)) {
                        // ensure first select exists and has options
                        const ensureRow = () => {
                            let first = coursesDiv.querySelector('select[name="courses[]"]');
                            if (!first) {
                                const addBtn = document.getElementById('addCourseDropdown');
                                addBtn && addBtn.click();
                                first = coursesDiv.querySelector('select[name="courses[]"]');
                            }
                            return first;
                        };
                        ensureRow();
                        if (courseData.data.length > 0) {
                            const selectsAll = () => coursesDiv.querySelectorAll('select[name="courses[]"]');
                            const all = selectsAll();
                            if (all.length) all[0].value = courseData.data[0]?.course_id || '';
                            for (let i = 1; i < courseData.data.length; i++) {
                                document.getElementById('addCourseDropdown')?.click();
                                const cur = selectsAll();
                                if (cur[i]) cur[i].value = courseData.data[i].course_id;
                            }
                        } else {
                            const first = coursesDiv.querySelector('select[name="courses[]"]');
                            if (first) first.selectedIndex = 0;
                        }
                    }
                } catch (err) { console.error('Failed to load student courses', err); }
            }

            setModalMode('edit');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addStudentModal')).show();
        } else {
            window.CRUD && CRUD.toastError && CRUD.toastError('Student not found');
        }
    } catch (e) {
        window.CRUD && CRUD.toastError && CRUD.toastError('Failed to load student: ' + e.message);
    } finally {
        window.CRUD && typeof CRUD.hideLoading === 'function' && CRUD.hideLoading();
    }
}

async function viewStudent(id) {
    if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer');
    try {
        // Ensure form/modal exists
        const form = document.getElementById('addStudentForm');
        const modalEl = document.getElementById('addStudentModal');
        if (!form || !modalEl) { window.CRUD && CRUD.toastError && CRUD.toastError('Student form not available'); return; }

        // Keep options fresh before setting values
        if (typeof window.populateStudentModalOptions === 'function') {
            await window.populateStudentModalOptions(true);
        }

        // Reset to a clean state before populating
        resetStudentFormForAdd();

        const data = await fetchJson(`api/students.php?action=get&id=${encodeURIComponent(id)}`);
        if (data.success && data.data) {
            const s = data.data;
            const setVal = (sel, val) => { const el = document.querySelector(sel); if (el) el.value = val ?? ''; };
            const setDate = (sel, val) => { const el = document.querySelector(sel); if (el) el.value = (val || '').slice(0, 10); };
            document.getElementById('studentId') && (document.getElementById('studentId').value = s.id || '');
            setVal('#addStudentForm [name="name"]', s.name);
            setVal('#addStudentForm [name="email"]', s.email);
            setVal('#addStudentForm [name="mobile"]', s.mobile || s.phone);
            setDate('#addStudentForm [name="dob"]', s.dob);
            setVal('#addStudentForm [name="education"]', s.education);
            setVal('#addStudentForm [name="college_name"]', s.college_name);
            setVal('#addStudentForm [name="father_name"]', s.father_name);
            setVal('#addStudentForm [name="address"]', s.address);
            setVal('#addStudentForm [name="pincode"]', s.pincode);
            setVal('#addStudentForm [name="state"]', s.state);
            setVal('#addStudentForm [name="city"]', s.city);
            setVal('#addStudentForm [name="area"]', s.area);
            const branchSel = document.getElementById('studentBranch'); if (branchSel) branchSel.value = s.branch_id ?? 0;
            const statusSel = document.querySelector('#addStudentForm [name="status"]'); if (statusSel) statusSel.value = (s.status == 1 || s.status === 'active') ? 'active' : 'inactive';
            const img = document.getElementById('studentPhotoPreview');
            if (img) {
                if (s.profile_photo) { img.src = STUDENT_UPLOAD_BASE + s.profile_photo; img.style.display = ''; }
                else { clearStudentPhotoPreview(); }
            }

            // Populate courses for view just like edit
            const coursesDiv = document.getElementById('courses-dynamic');
            if (coursesDiv) {
                while (coursesDiv.children.length > 1) coursesDiv.removeChild(coursesDiv.lastChild);
                try {
                    const courseData = await fetchJson(`api/students.php?action=get_courses&id=${encodeURIComponent(s.id)}`);
                    if (courseData.success && Array.isArray(courseData.data)) {
                        const ensureRow = () => {
                            let first = coursesDiv.querySelector('select[name="courses[]"]');
                            if (!first) {
                                const addBtn = document.getElementById('addCourseDropdown');
                                addBtn && addBtn.click();
                                first = coursesDiv.querySelector('select[name="courses[]"]');
                            }
                            return first;
                        };
                        ensureRow();
                        const selectsAll = () => coursesDiv.querySelectorAll('select[name="courses[]"]');
                        if (courseData.data.length > 0) {
                            const all = selectsAll();
                            if (all.length) all[0].value = courseData.data[0]?.course_id || '';
                            for (let i = 1; i < courseData.data.length; i++) {
                                document.getElementById('addCourseDropdown')?.click();
                                const cur = selectsAll();
                                if (cur[i]) cur[i].value = courseData.data[i].course_id;
                            }
                        } else {
                            const first = coursesDiv.querySelector('select[name="courses[]"]');
                            if (first) first.selectedIndex = 0;
                        }
                    }
                } catch (err) { console.error('Failed to load student courses (view)', err); }
            }

            setModalMode('view');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addStudentModal')).show();
        } else {
            window.CRUD && CRUD.toastError && CRUD.toastError('Student not found');
        }
    } catch (e) {
        window.CRUD && CRUD.toastError && CRUD.toastError('Failed to load student: ' + e.message);
    } finally {
        window.CRUD && typeof CRUD.hideLoading === 'function' && CRUD.hideLoading();
    }
}

async function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student?')) return;
    window.CRUD && typeof CRUD.showLoading === 'function' && CRUD.showLoading('tableContainer');
    try {
        const params = new URLSearchParams(); params.append('id', id);
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken && metaToken.content) params.append('csrf_token', metaToken.content);
        const data = await CRUD.post('api/students.php?action=delete', params);
        if (data.success) {
            window.CRUD && window.CRUD.toastSuccess && window.CRUD.toastSuccess(data.message || 'Deleted');
            refreshTable();
        } else {
            window.CRUD && CRUD.toastError && CRUD.toastError('Delete failed: ' + (data.message || data.error || 'Unknown error'));
        }
    } catch (e) {
        window.CRUD && CRUD.toastError && CRUD.toastError('Delete request failed: ' + e.message);
    } finally {
        window.CRUD && typeof CRUD.hideLoading === 'function' && CRUD.hideLoading();
    }
}

async function saveStudent() {
    const form = document.getElementById('addStudentForm');
    const formData = new FormData(form);
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken && metaToken.content) formData.set('csrf_token', metaToken.content);
    if (!formData.get('name')) { window.CRUD && CRUD.toastError && CRUD.toastError('Name is required'); return; }
    if (!formData.get('email')) { window.CRUD && CRUD.toastError && CRUD.toastError('Email is required'); return; }
    if (!formData.get('branch_id')) formData.set('branch_id', 0);
    const modalEl = document.getElementById('addStudentModal');
    window.CRUD && typeof CRUD.modalLoadingStart === 'function' && CRUD.modalLoadingStart(modalEl);
    try {
        const id = formData.get('id');
        const action = id ? 'update' : 'create';
        const res = await fetch('api/students.php?action=' + action, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            window.CRUD && window.CRUD.toastSuccess && window.CRUD.toastSuccess(data.message || 'Saved');
            refreshTable();
        } else {
            window.CRUD && CRUD.toastError && CRUD.toastError('Save failed: ' + (data.message || data.error || 'Unknown error'));
        }
    } catch (e) {
        window.CRUD && CRUD.toastError && CRUD.toastError('Save request failed: ' + e.message);
    } finally {
        window.CRUD && typeof CRUD.modalLoadingStop === 'function' && CRUD.modalLoadingStop(modalEl);
    }
}

// Photo preview
document.addEventListener('change', function(e){
    if (e.target && e.target.id === 'studentPhotoInput') {
        const input = e.target; const file = input.files && input.files[0];
        const img = document.getElementById('studentPhotoPreview');
        const removeBtn = document.getElementById('removeStudentPhoto');
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => { img.src = ev.target.result; img.style.display = ''; if (removeBtn) removeBtn.style.display = ''; };
            reader.readAsDataURL(file);
        } else { clearStudentPhotoPreview(); }
    }
});

// Remove stored or pending photo
document.addEventListener('click', async function(e){
    if (e.target && e.target.id === 'removeStudentPhoto') {
        const id = document.getElementById('studentId').value;
        const input = document.getElementById('studentPhotoInput'); if (input) input.value = '';
        clearStudentPhotoPreview();
        if (id) {
            try {
                const params = new URLSearchParams(); params.append('id', id);
                const res = await fetch('api/students.php?action=delete-photo', { method: 'POST', body: params });
                const data = await res.json();
                if (!data.success) window.CRUD && CRUD.toastError && CRUD.toastError(data.message || 'Failed to remove');
                else window.CRUD && CRUD.toastSuccess && CRUD.toastSuccess('Photo removed');
            } catch (err) { window.CRUD && CRUD.toastError && CRUD.toastError('Remove failed: ' + err.message); }
        }
    }
});

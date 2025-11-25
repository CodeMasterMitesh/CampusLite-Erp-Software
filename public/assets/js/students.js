// students.js - extracted from students.php
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with column filters
    const dt = initAdvancedTable('#students-table');

    // page fade-in
    const container = document.querySelector('.dashboard-container');
    if (container) container.classList.add('show');

    // Prevent table-row hover effects from shifting layout (same small inline style as before)
    const style = document.createElement('style');
    style.innerHTML = `
        #students-table tbody tr { transition: none !important; }
        #students-table tbody tr:hover { transform: none !important; box-shadow: none !important; margin: 0 !important; }
        #students-table thead tr.filters input { width: 100%; }
    `;
    document.head.appendChild(style);

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
            // Try to open modal via common helper if available
            if (typeof showAddModal === 'function') {
                showAddModal('addStudentModal', 'addStudentForm');
            } else {
                const btn = document.querySelector('[data-bs-target="#addStudentModal"]');
                btn && btn.click();
            }
        }
    });

    // Selection handling for delete-selected header button
    const selectAllStudents = document.getElementById('select-all-students');
    const headerDeleteBtn = document.getElementById('delete-selected-students-header');
    function updateSelectionUI() {
        const any = !!document.querySelectorAll('#students-table tbody .row-select:checked').length;
        if (headerDeleteBtn) {
            // Hide the header delete button until a selection exists
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
});

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
    } else {
        Array.from(form.elements).forEach(el => el.disabled = false);
        saveBtn && (saveBtn.style.display = '');
        document.querySelector('#addStudentModal .modal-title') && (document.querySelector('#addStudentModal .modal-title').innerText = mode === 'edit' ? 'Edit Student' : 'Add New Student');
    }
}

// Fetch and populate student for edit
async function editStudent(id) {
    if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer');
    try {
        const data = await fetchJson(`api/students.php?action=get&id=${encodeURIComponent(id)}`);
        if (data.success && data.data) {
            const s = data.data;
            document.getElementById('studentId').value = s.id || '';
            document.querySelector('#addStudentForm [name="name"]').value = s.name || '';
            document.querySelector('#addStudentForm [name="email"]').value = s.email || '';
            document.querySelector('#addStudentForm [name="mobile"]').value = s.mobile || s.phone || '';
            document.querySelector('#addStudentForm [name="dob"]').value = s.dob || '';
            document.querySelector('#addStudentForm [name="education"]').value = s.education || '';
            document.querySelector('#addStudentForm [name="college_name"]').value = s.college_name || '';
            document.querySelector('#addStudentForm [name="father_name"]').value = s.father_name || '';
            document.querySelector('#addStudentForm [name="address"]').value = s.address || '';
            document.querySelector('#addStudentForm [name="pincode"]').value = s.pincode || '';
            document.querySelector('#addStudentForm [name="state"]').value = s.state || '';
            document.querySelector('#addStudentForm [name="city"]').value = s.city || '';
            document.querySelector('#addStudentForm [name="area"]').value = s.area || '';
            document.getElementById('studentBranch') && (document.getElementById('studentBranch').value = s.branch_id ?? 0);
            document.querySelector('#addStudentForm [name="status"]').value = (s.status == 1 || s.status === 'active') ? 'active' : 'inactive';

            // Courses: fetch selected courses for student (AJAX)
            const coursesDiv = document.getElementById('courses-dynamic');
            if (coursesDiv) {
                while (coursesDiv.children.length > 1) coursesDiv.removeChild(coursesDiv.lastChild);
                try {
                    const courseData = await fetchJson(`api/students.php?action=get_courses&id=${encodeURIComponent(s.id)}`);
                    if (courseData.success && Array.isArray(courseData.data)) {
                        const selects = coursesDiv.querySelectorAll('select[name="courses[]"]');
                        if (selects.length === 0) {
                            // create one row by triggering add
                            const addBtn = document.getElementById('addCourseDropdown');
                            addBtn && addBtn.click();
                        }
                        const updatedSelects = coursesDiv.querySelectorAll('select[name="courses[]"]');
                        if (courseData.data.length > 0) {
                            updatedSelects[0].value = courseData.data[0] ? courseData.data[0].course_id : '';
                            for (let i = 1; i < courseData.data.length; i++) {
                                document.getElementById('addCourseDropdown').click();
                                const cur = coursesDiv.querySelectorAll('select[name="courses[]"]');
                                cur[i].value = courseData.data[i].course_id;
                            }
                        } else {
                            updatedSelects[0].selectedIndex = 0;
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
        const data = await fetchJson(`api/students.php?action=get&id=${encodeURIComponent(id)}`);
        if (data.success && data.data) {
            const s = data.data;
            document.getElementById('studentId').value = s.id || '';
            document.querySelector('#addStudentForm [name="name"]').value = s.name || '';
            document.querySelector('#addStudentForm [name="email"]').value = s.email || '';
            document.querySelector('#addStudentForm [name="mobile"]').value = s.mobile || s.phone || '';
            document.getElementById('studentBranch') && (document.getElementById('studentBranch').value = s.branch_id ?? 0);
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
        const res = await fetch('api/students.php?action=delete', { method: 'POST', body: params });
        const data = await res.json();
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

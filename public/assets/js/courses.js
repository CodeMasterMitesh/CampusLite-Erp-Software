// courses.js - centralised page logic for courses.php
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable with column filters
    initAdvancedTable('#courses-table');

    // page fade-in
    const container = document.querySelector('.dashboard-container');
    if (container) container.classList.add('show');

    // Add More Subject Dropdown functionality
    const addBtn = document.getElementById('addSubjectDropdown');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const subjectsDiv = document.getElementById('subjects-dynamic');
            const firstRow = subjectsDiv.querySelector('.subject-row');
            if (!firstRow) return;
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('select') && (newRow.querySelector('select').selectedIndex = 0);
            subjectsDiv.appendChild(newRow);
        });
    }

    // Delete subject row functionality
    const subjectsDiv = document.getElementById('subjects-dynamic');
    if (subjectsDiv) {
        subjectsDiv.addEventListener('click', function(e) {
            if (e.target.closest('.delete-subject-btn')) {
                const row = e.target.closest('.subject-row');
                if (this.querySelectorAll('.subject-row').length > 1) row.remove();
            }
        });
    }
});

function showAddCourseModal() {
    // use shared helper to reset and show modal
    if (typeof showAddModal === 'function') {
        showAddModal('addCourseModal', 'addCourseForm');
    } else {
        const form = document.getElementById('addCourseForm');
        form && form.reset();
        const subjectsDiv = document.getElementById('subjects-dynamic');
        if (subjectsDiv) {
            while (subjectsDiv.children.length > 1) subjectsDiv.removeChild(subjectsDiv.lastChild);
            const sel = subjectsDiv.querySelector('select[name="subjects[]"]');
            sel && (sel.selectedIndex = 0);
        }
        document.getElementById('courseId') && (document.getElementById('courseId').value = '');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addCourseModal')).show();
    }
    document.querySelector('#addCourseModal .modal-title') && (document.querySelector('#addCourseModal .modal-title').innerText = 'Add Course');
}

async function saveCourse() {
    const form = document.getElementById('addCourseForm');
    const formData = new FormData(form);
    const subjects = Array.from(form.querySelectorAll('select[name="subjects[]"]')).map(sel => sel.value).filter(v => v);
    formData.delete('subjects[]');
    subjects.forEach(sid => formData.append('subjects[]', sid));
    const id = formData.get('id');
    const action = id ? 'update' : 'create';
    try {
        const res = await fetch('api/courses.php?action=' + action, { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addCourseModal')).hide();
            location.reload();
        } else {
            alert('Save failed: ' + (data.message || data.error || 'Unknown error'));
        }
    } catch (e) {
        alert('Save request failed: ' + e.message);
    }
}

async function editCourse(id) {
    try {
        const data = await fetchJson('api/courses.php?action=get&id=' + encodeURIComponent(id));
        if (data.success && data.data) {
            const c = data.data;
            document.getElementById('courseId').value = c.id || '';
            document.querySelector('#addCourseForm [name="branch_id"]').value = c.branch_id ? String(c.branch_id) : '0';
            document.querySelector('#addCourseForm [name="title"]').value = c.title || '';
            document.querySelector('#addCourseForm [name="description"]').value = c.description || '';
            document.querySelector('#addCourseForm [name="total_fee"]').value = c.total_fee || '';
            document.querySelector('#addCourseForm [name="duration_months"]').value = c.duration_months || '';
            // Subjects
            const subjectsDiv = document.getElementById('subjects-dynamic');
            while (subjectsDiv.children.length > 1) subjectsDiv.removeChild(subjectsDiv.lastChild);
            try {
                const subData = await fetchJson('api/courses.php?action=get_subjects&id=' + encodeURIComponent(c.id));
                if (subData.success && Array.isArray(subData.data)) {
                    const subs = subData.data;
                    let selects = subjectsDiv.querySelectorAll('select[name="subjects[]"]');
                    if (selects.length === 0) { document.getElementById('addSubjectDropdown').click(); selects = subjectsDiv.querySelectorAll('select[name="subjects[]"]'); }
                    if (subs.length > 0) {
                        selects[0].value = subs[0].subject_id || '';
                        for (let i = 1; i < subs.length; i++) {
                            document.getElementById('addSubjectDropdown').click();
                            const currentSelects = subjectsDiv.querySelectorAll('select[name="subjects[]"]');
                            currentSelects[i].value = subs[i].subject_id || '';
                        }
                    } else {
                        selects[0].selectedIndex = 0;
                    }
                }
            } catch (err) { console.error('Failed to load subjects for course', err); }
            Array.from(document.getElementById('addCourseForm').elements).forEach(el => el.disabled = false);
            document.querySelector('#addCourseModal .btn-primary').style.display = '';
            document.querySelector('#addCourseModal .modal-title').innerText = 'Edit Course';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addCourseModal')).show();
        } else {
            alert('Course not found');
        }
    } catch (e) {
        alert('Failed to load course: ' + e.message);
    }
}

async function viewCourse(id) {
    await editCourse(id);
    Array.from(document.getElementById('addCourseForm').elements).forEach(el => el.disabled = true);
    document.querySelector('#addCourseModal .btn-primary').style.display = 'none';
    document.querySelector('#addCourseModal .modal-title').innerText = 'View Course';
    const subjectsDiv = document.getElementById('subjects-dynamic');
    const selects = subjectsDiv.querySelectorAll('select[name="subjects[]"]');
    selects.forEach(sel => sel.disabled = true);
}

async function deleteCourse(id) {
    if (!confirm('Are you sure you want to delete this course?')) return;
    try {
        const formData = new FormData(); formData.append('id', id);
        const res = await fetch('api/courses.php?action=delete', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) location.reload(); else alert('Delete failed: ' + (data.message || data.error || 'Unknown error'));
    } catch (e) { alert('Delete request failed: ' + e.message); }
}

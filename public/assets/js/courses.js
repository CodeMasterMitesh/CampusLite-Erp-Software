// courses.js - Course management page logic

// Wrap in IIFE to prevent redeclaration, but expose functions to window for AJAX calls
(function() {
    // Use window object for variables that persist across AJAX loads
    if (!window.coursePageState) {
        window.coursePageState = {
            allSubjects: []
        };
    }

    const state = window.coursePageState;

    function initCourses() {
        try { initAdvancedTable('#courses-table'); } catch(e) { console.error('initCourses: initAdvancedTable failed', e); }
        const container = document.querySelector('.dashboard-container');
        if (container) container.classList.add('show');
        
        loadAllSubjects();
        initSubjectRows();
        initSearchInput();
    }

    async function loadAllSubjects() {
        try {
            const res = await fetch('api/subjects.php?action=list', {credentials: 'same-origin'});
            const json = await res.json();
            if (json.success && Array.isArray(json.data)) {
                // Clear and repopulate the array instead of reassigning
                state.allSubjects.length = 0;
                state.allSubjects.push(...json.data);
            }
        } catch(e) { console.error('Failed to load subjects', e); }
    }

    function createSubjectRow(initialData) {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex gap-2 mb-2 align-items-center subject-row';
        
        const select = document.createElement('select');
        select.className = 'form-control form-control-sm subject-select';
        select.name = 'subjects[]';
        
        const blankOpt = document.createElement('option');
        blankOpt.value = '';
        blankOpt.textContent = '-- Select Subject --';
        select.appendChild(blankOpt);
        
        state.allSubjects.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.title || ('Subject ' + s.id);
            select.appendChild(opt);
        });
        
        if (initialData && initialData.id) {
            select.value = initialData.id;
        }
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            wrapper.remove();
        });
        
        wrapper.appendChild(select);
        wrapper.appendChild(removeBtn);
        return wrapper;
    }

    function initSubjectRows() {
        const container = document.getElementById('courseSubjectsContainer');
        const addBtn = document.getElementById('addSubjectRowBtn');
        if (!container || !addBtn) return;
        
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            container.appendChild(createSubjectRow());
        });
    }

    function initSearchInput() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;
        
        let searchTimeout;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const v = e.target.value.toLowerCase();
                document.querySelectorAll('#courses-table tbody tr').forEach(r => {
                    r.style.display = r.innerText.toLowerCase().includes(v) ? '' : 'none';
                });
            }, 200);
        });
    }

    function showAddCourseModal() {
        // Reset form
        const form = document.getElementById('addCourseForm');
        if (form) form.reset();
        
        // Clear course ID
        const courseId = document.getElementById('courseId');
        if (courseId) courseId.value = '';
        
        // Reset subjects container
        const container = document.getElementById('courseSubjectsContainer');
        if (container) {
            container.innerHTML = '';
            container.appendChild(createSubjectRow());
        }
        
        // Clear file display
        const fileDisplay = document.getElementById('currentFileDisplay');
        if (fileDisplay) fileDisplay.innerHTML = '';
        
        // ENABLE all form fields
        if (form) {
            Array.from(form.elements).forEach(el => el.disabled = false);
        }
        
        // Update modal title and button
        const titleEl = document.querySelector('#addCourseModal .modal-title');
        if (titleEl) titleEl.innerHTML = '<i class="fas fa-book me-2"></i> Add Course';
        
        const saveBtn = document.getElementById('saveCourseBtn');
        if (saveBtn) {
            saveBtn.textContent = 'Save Course';
            saveBtn.style.display = ''; // Make sure button is visible
        }
        
        // Show modal
        const modalEl = document.getElementById('addCourseModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
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

    function refreshTable() {
        showLoading();
        setTimeout(() => location.reload(), 600);
    }

    function exportToExcel() {
        showLoading();
        setTimeout(() => {
            window.location.href = '?page=courses&export=excel';
            hideLoading();
        }, 800);
    }

    function printTable() {
        const table = document.getElementById('courses-table').cloneNode(true);
        const w = window.open('', '_blank');
        w.document.write(`<html><head><title>Courses</title><style>body{font-family:Arial}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}</style></head><body><h2>Courses</h2>${table.outerHTML}<p>Generated on: ${new Date().toLocaleDateString()}</p></body></html>`);
        w.document.close();
        w.print();
    }

    async function editCourse(id) {
        if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
        try {
            const res = await CRUD.get(`api/courses.php?action=get&id=${encodeURIComponent(id)}`);
            if (res.success && res.data) {
                const c = res.data;
                document.getElementById('courseId').value = c.id || '';
                document.querySelector('#addCourseForm [name="title"]').value = c.title || '';
                document.querySelector('#addCourseForm [name="branch_id"]').value = c.branch_id || '';
                document.querySelector('#addCourseForm [name="total_fee"]').value = c.total_fee || '';
                document.querySelector('#addCourseForm [name="duration_months"]').value = c.duration_months || '';
                
                // Load and display subjects
                const subjRes = await CRUD.get(`api/courses.php?action=get_subjects&id=${encodeURIComponent(id)}`);
                const container = document.getElementById('courseSubjectsContainer');
                container.innerHTML = '';
                
                if (subjRes.success && Array.isArray(subjRes.data)) {
                    subjRes.data.forEach(s => {
                        const row = createSubjectRow({id: s.subject_id || s.id});
                        container.appendChild(row);
                    });
                }
                
                if (container.querySelectorAll('.subject-row').length === 0) {
                    container.appendChild(createSubjectRow());
                }
                
                // Display current file if exists
                const fileDisplay = document.getElementById('currentFileDisplay');
                if (fileDisplay && c.file_path) {
                    const fileName = c.file_name || c.file_path.split('/').pop();
                    fileDisplay.innerHTML = `
                        <div class="alert alert-info d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-file me-2"></i> Current file: <strong>${fileName}</strong></span>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCourseFile(${c.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    `;
                } else if (fileDisplay) {
                    fileDisplay.innerHTML = '';
                }
                
                // ENABLE form fields for editing
                Array.from(document.getElementById('addCourseForm').elements).forEach(el => el.disabled = false);
                
                // Update modal title and button
                const titleEl = document.querySelector('#addCourseModal .modal-title');
                if (titleEl) titleEl.innerHTML = '<i class="fas fa-book me-2"></i> Edit Course';
                
                const saveBtn = document.getElementById('saveCourseBtn');
                if (saveBtn) {
                    saveBtn.textContent = 'Update Course';
                    saveBtn.style.display = ''; // Make sure button is visible
                }
                
                const modalEl = document.getElementById('addCourseModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                window.CRUD && CRUD.toastError && CRUD.toastError('Course not found');
            }
        } catch (e) {
            window.CRUD && CRUD.toastError && CRUD.toastError('Failed to load: ' + e.message);
        } finally {
            if (window.CRUD && CRUD.hideLoading) CRUD.hideLoading();
        }
    }

    async function deleteCourse(id) {
        if (!confirm('Delete course ' + id + '?')) return;
        if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
        try {
            const params = new URLSearchParams();
            params.append('id', id);
            const res = await CRUD.post('api/courses.php?action=delete', params);
            if (res.success) {
                window.CRUD && CRUD.toastSuccess && CRUD.toastSuccess('Deleted');
                refreshTable();
            } else {
                window.CRUD && CRUD.toastError && CRUD.toastError('Delete failed: ' + (res.message || 'Unknown error'));
            }
        } catch (e) {
            window.CRUD && CRUD.toastError && CRUD.toastError('Delete failed: ' + e.message);
        } finally {
            if (window.CRUD && CRUD.hideLoading) CRUD.hideLoading();
        }
    }

    async function saveCourse() {
        const form = document.getElementById('addCourseForm');
        const params = new FormData(form);
        if (!params.get('title')) {
            window.CRUD && CRUD.toastError && CRUD.toastError('Title required');
            return;
        }
        
        if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
        try {
            const id = params.get('id');
            const action = id ? 'update' : 'create';
            const res = await CRUD.post('api/courses.php?action=' + action, params);
            if (res.success) {
                const modalEl = document.getElementById('addCourseModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
                window.CRUD && CRUD.toastSuccess && CRUD.toastSuccess('Saved');
                refreshTable();
            } else {
                window.CRUD && CRUD.toastError && CRUD.toastError('Save failed: ' + (res.message || 'Unknown error'));
            }
        } catch (e) {
            window.CRUD && CRUD.toastError && CRUD.toastError('Request failed: ' + e.message);
        } finally {
            if (window.CRUD && CRUD.hideLoading) CRUD.hideLoading();
        }
    }

    async function viewCourse(id) {
        if (window.CRUD && CRUD.showLoading) CRUD.showLoading('tableContainer');
        try {
            const res = await CRUD.get(`api/courses.php?action=get&id=${encodeURIComponent(id)}`);
            if (res.success && res.data) {
                const c = res.data;
                document.getElementById('courseId').value = c.id || '';
                document.querySelector('#addCourseForm [name="title"]').value = c.title || '';
                document.querySelector('#addCourseForm [name="branch_id"]').value = c.branch_id || '';
                document.querySelector('#addCourseForm [name="total_fee"]').value = c.total_fee || '';
                document.querySelector('#addCourseForm [name="duration_months"]').value = c.duration_months || '';
                
                // Load subjects
                const subjRes = await CRUD.get(`api/courses.php?action=get_subjects&id=${encodeURIComponent(id)}`);
                const container = document.getElementById('courseSubjectsContainer');
                container.innerHTML = '';
                
                if (subjRes.success && Array.isArray(subjRes.data)) {
                    subjRes.data.forEach(s => {
                        const row = createSubjectRow({id: s.subject_id || s.id});
                        container.appendChild(row);
                    });
                }
                
                if (container.querySelectorAll('.subject-row').length === 0) {
                    container.appendChild(createSubjectRow());
                }
                
                // Display file
                const fileDisplay = document.getElementById('currentFileDisplay');
                if (fileDisplay && c.file_path) {
                    const fileName = c.file_name || c.file_path.split('/').pop();
                    fileDisplay.innerHTML = `
                        <div class="alert alert-info d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-file me-2"></i> ${fileName}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewCourseFile(${c.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    `;
                } else if (fileDisplay) {
                    fileDisplay.innerHTML = '';
                }
                
                // Disable form
                Array.from(document.getElementById('addCourseForm').elements).forEach(el => el.disabled = true);
                
                // Update modal
                const titleEl = document.querySelector('#addCourseModal .modal-title');
                if (titleEl) titleEl.innerHTML = '<i class="fas fa-book me-2"></i> View Course';
                
                const saveBtn = document.getElementById('saveCourseBtn');
                if (saveBtn) saveBtn.style.display = 'none';
                
                const modalEl = document.getElementById('addCourseModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                window.CRUD && CRUD.toastError && CRUD.toastError('Course not found');
            }
        } catch (e) {
            window.CRUD && CRUD.toastError && CRUD.toastError('Failed to load: ' + e.message);
        } finally {
            if (window.CRUD && CRUD.hideLoading) CRUD.hideLoading();
        }
    }

    async function viewCourseFile(courseId) {
        try {
            const res = await CRUD.get(`api/courses.php?action=get&id=${encodeURIComponent(courseId)}`);
            if (res.success && res.data && res.data.file_path) {
                const filePath = res.data.file_path;
                const fileName = res.data.file_name || filePath.split('/').pop();
                const fileExt = fileName.split('.').pop().toLowerCase();
                
                const modalEl = document.getElementById('fileViewerModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                const content = document.getElementById('fileViewerContent');
                const downloadBtn = document.getElementById('downloadFileBtn');
                
                // Set download link
                downloadBtn.href = filePath;
                downloadBtn.download = fileName;
                
                // Display based on file type
                if (fileExt === 'pdf') {
                    content.innerHTML = `<iframe src="${filePath}" style="width:100%; height:600px; border:none;"></iframe>`;
                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                    content.innerHTML = `<img src="${filePath}" class="img-fluid" alt="${fileName}">`;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-file fa-5x text-muted mb-3"></i>
                            <h5>${fileName}</h5>
                            <p class="text-muted">Preview not available for this file type</p>
                            <a href="${filePath}" download="${fileName}" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    `;
                }
                
                modal.show();
            } else {
                window.CRUD && CRUD.toastError && CRUD.toastError('File not found');
            }
        } catch (e) {
            window.CRUD && CRUD.toastError && CRUD.toastError('Failed to load file: ' + e.message);
        }
    }

    // Expose functions to window for global access
    window.initCourses = initCourses;
    window.showAddCourseModal = showAddCourseModal;
    window.editCourse = editCourse;
    window.viewCourse = viewCourse;
    window.viewCourseFile = viewCourseFile;
    window.deleteCourse = deleteCourse;
    window.saveCourse = saveCourse;
    window.exportToExcel = exportToExcel;
    window.printTable = printTable;

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCourses);
    } else {
        try { initCourses(); } catch (e) { console.error('initCourses immediate failed', e); }
    }
})();

// Common helpers for DataTables and modal handling
function initAdvancedTable(tableSelector) {
    const table = $(tableSelector);
    if (!table.length) return null;
    
    // Prevent reinitialization: if already a DataTable, destroy and recreate
    if ($.fn.DataTable && $.fn.DataTable.isDataTable && $.fn.DataTable.isDataTable(tableSelector)) {
        table.DataTable().destroy();
    }
    
    const thead = table.find('thead');
    
    // Remove existing filter row if any
    thead.find('tr.filters').remove();
    
    // Add filter row
    const filterRow = $('<tr>').addClass('filters');
    const headerCells = thead.find('tr').first().children();
    
    headerCells.each(function() {
        const th = $('<th>');
        // Skip checkboxes and action columns
        if ($(this).find('input[type="checkbox"]').length || 
            $(this).text().trim().toLowerCase() === 'actions' ||
            $(this).hasClass('no-filter')) {
            th.html('');
        } else {
            th.html('<input type="text" class="form-control form-control-sm" placeholder="Search...">');
        }
        filterRow.append(th);
    });
    
    thead.append(filterRow);
    
    // Initialize DataTable
    const dataTable = table.DataTable({ 
        dom: 'lrtip', 
        orderCellsTop: true,
        fixedHeader: true, 
        pageLength: 10, 
        lengthMenu: [10, 25, 50, 100], 
        responsive: false,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: true,
        deferRender: true,
        columnDefs: [
            { orderable: false, targets: [0, -1] },
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: -1 }
        ]
    });
    
    // Adjust columns
    try { 
        dataTable.columns.adjust().draw(false); 
    } catch(e) { 
        console.error('Column adjust failed:', e); 
    }
    
    // Handle window resize
    try { 
        window.addEventListener('resize', function() { 
            try { 
                dataTable.columns.adjust(); 
            } catch(e) {} 
        }); 
    } catch(e) {}
    
    // Setup column search with proper event delegation
    thead.find('tr.filters th').each(function(index) {
        const input = $(this).find('input');
        if (input.length) {
            input.off('keyup change').on('keyup change', function() {
                const searchValue = this.value;
                if (dataTable.column(index).search() !== searchValue) {
                    dataTable.column(index).search(searchValue).draw();
                }
            });
        }
    });
    
    return dataTable;
}

function showAddModal(modalId, formId, opts = {}) {
    const form = document.getElementById(formId);
    if (form) form.reset();
    // reset modal title and save button to defaults for Add mode
    try {
        const titleEl = document.getElementById('assignmentModalTitle');
        if (titleEl) titleEl.textContent = opts.title || 'Add Assignment';
        const saveBtn = document.getElementById('assignmentSaveBtn');
        if (saveBtn) saveBtn.textContent = opts.saveLabel || 'Save';
    } catch(e) { /* ignore if elements not present */ }
    // ensure CSRF hidden input exists in the form (for non-AJAX submissions)
    try {
        if (form) {
            const tokenLookup = (typeof window.getCsrfToken === 'function') ? window.getCsrfToken : function(){ return (window.__csrfToken || null); };
            let csrfInput = form.querySelector('input[name="csrf_token"]');
            const tokenValue = tokenLookup() || '';
            if (!csrfInput) {
                csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = tokenValue;
                form.appendChild(csrfInput);
            } else {
                csrfInput.value = tokenValue || csrfInput.value;
            }
        }
    } catch(e) { /* ignore */ }
    // reset selects inside subjects-dynamic if exists
    const subjectsDiv = document.getElementById('subjects-dynamic');
    if (subjectsDiv) {
        while (subjectsDiv.children.length > 1) subjectsDiv.removeChild(subjectsDiv.lastChild);
        const sel = subjectsDiv.querySelector('select[name="subjects[]"]');
        if (sel) sel.selectedIndex = 0;
    }
    // enable fields
    if (form) Array.from(form.elements).forEach(el => el.disabled = false);
    const modalEl = document.getElementById(modalId);
    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

// Declarative modal opener: listens for elements with data-modal-target
(function attachDeclarativeModalHandler(){
    function handleClick(e) {
        const el = e.target.closest && e.target.closest('[data-modal-target]');
        if (!el) return;
        const modalId = el.getAttribute('data-modal-target');
        const formId = el.getAttribute('data-modal-form') || null;
        if (modalId) {
            e.preventDefault();
            try { showAddModal(modalId, formId); } catch(err) { console.error('showAddModal failed', err); }
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ document.body.addEventListener('click', handleClick); });
    else document.body.addEventListener('click', handleClick);
})();

// Global refresh helper: clears cached lists and notifies listeners
function refreshGlobalLists() {
    try {
        delete window._cachedBranches;
        delete window._cachedCourses;
        const ev = new CustomEvent('globalListsRefreshed');
        window.dispatchEvent(ev);
    } catch(e) { console.error('refreshGlobalLists failed', e); }
}

// Notify user when lists are refreshed
window.addEventListener('globalListsRefreshed', function(){
    try {
        if (window.CRUD && typeof window.CRUD.toastSuccess === 'function') {
            window.CRUD.toastSuccess('Branch and course lists refreshed');
        } else {
            // fallback small toast
            const el = document.createElement('div');
            el.style.position = 'fixed'; el.style.top = '1rem'; el.style.right = '1rem'; el.style.zIndex = 2000;
            el.className = 'alert alert-success'; el.innerText = 'Branch and course lists refreshed';
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }
    } catch(e) { console.error('globalListsRefreshed handler failed', e); }
});

// Utility: fetch JSON safe
async function fetchJson(url, opts) {
    const defaultOpts = { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } };
    opts = opts || {};
    // merge headers
    opts.headers = Object.assign({}, defaultOpts.headers, opts.headers || {});
    if (!opts.credentials) opts.credentials = defaultOpts.credentials;
    const method = (opts.method || 'GET').toUpperCase();
    if (method !== 'GET') {
        const tokenLookup = (typeof window.getCsrfToken === 'function') ? window.getCsrfToken : function(){ return (window.__csrfToken || null); };
        const token = tokenLookup();
        if (token) opts.headers['X-CSRF-Token'] = token;
    }

    const res = await fetch(url, opts);
    const text = await res.text();
    // try parse JSON
    let data = null;
    try {
        data = text ? JSON.parse(text) : null;
    } catch (e) {
        // not JSON
        if (!res.ok) {
            const err = new Error(text || res.statusText || 'Request failed');
            err.status = res.status;
            throw err;
        }
        return text;
    }

    if (!res.ok) {
        const err = new Error((data && data.message) ? data.message : res.statusText || 'Request failed');
        err.status = res.status;
        err.data = data;
        // If unauthorized or forbidden, redirect to login page to re-authenticate
        if (err.status === 401 || err.status === 403) {
            try { window.location.href = '/login.php'; } catch(e) {}
        }
        throw err;
    }
    return data;
}

// Lightweight card fade-in animation to keep UI lively after layout refactor
document.addEventListener('DOMContentLoaded', function(){
    try {
        const cards = document.querySelectorAll('.card');
        if (!cards.length || typeof IntersectionObserver === 'undefined') return;
        const observer = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if (entry.isIntersecting) {
                    entry.target.classList.add('card-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        cards.forEach(function(card){
            card.classList.add('card-prefade');
            observer.observe(card);
        });
    } catch (err) {
        console.warn('card animation init failed', err);
    }
});

// Shared media preview (images or PDFs) for photo/doc links
(function initMediaPreview(){
    const modalEl = document.getElementById('mediaPreviewModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    const bodyEl = document.getElementById('mediaPreviewBody');
    const titleEl = document.getElementById('mediaPreviewTitle');
    const metaEl = document.getElementById('mediaPreviewMeta');
    const openEl = document.getElementById('mediaPreviewOpenOriginal');
    const placeholder = document.getElementById('mediaPreviewPlaceholder');

    function inferType(url, explicit) {
        if (explicit) return explicit;
        const cleanUrl = (url || '').split('?')[0].split('#')[0];
        const ext = cleanUrl.substring(cleanUrl.lastIndexOf('.') + 1).toLowerCase();
        if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) return 'image';
        if (ext === 'pdf') return 'pdf';
        return '';
    }

    function renderContent(url, type, title) {
        if (!bodyEl) return;
        bodyEl.innerHTML = '';
        if (placeholder) placeholder.remove();
        const resolvedType = inferType(url, type);
        const filename = (url || '').split('/').pop();

        if (titleEl) titleEl.textContent = title || 'Preview';
        if (metaEl) metaEl.textContent = filename || '';
        if (openEl) {
            openEl.href = url;
            openEl.style.display = url ? 'inline-flex' : 'none';
        }

        if (resolvedType === 'image') {
            const img = document.createElement('img');
            img.src = url;
            img.alt = title || 'Preview image';
            img.style.maxWidth = '100%';
            img.style.maxHeight = '80vh';
            img.style.objectFit = 'contain';
            img.className = 'shadow-sm rounded';
            bodyEl.appendChild(img);
            return true;
        }

        if (resolvedType === 'pdf') {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.title = title || 'Preview document';
            iframe.style.width = '100%';
            iframe.style.height = '80vh';
            iframe.className = 'border rounded';
            bodyEl.appendChild(iframe);
            return true;
        }

        return false;
    }

    function handleClick(e) {
        const link = e.target.closest && e.target.closest('.media-preview-link');
        if (!link) return;
        const url = link.getAttribute('data-preview-url') || link.getAttribute('href') || '';
        if (!url) return;
        e.preventDefault();
        const type = link.getAttribute('data-preview-type') || '';
        const title = link.getAttribute('data-preview-title') || 'Preview';

        const rendered = renderContent(url, type, title);
        if (!rendered) {
            window.open(url, '_blank', 'noopener');
            return;
        }
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    document.addEventListener('click', handleClick, true);
})();

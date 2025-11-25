// Common helpers for DataTables and modal handling
function initAdvancedTable(tableSelector) {
    const table = $(tableSelector);
    const thead = table.find('thead');
    const filterRow = $('<tr>').addClass('filters');
    thead.find('tr').first().children().each(function() {
        const th = $('<th>');
        if ($(this).find('input[type="checkbox"]').length || $(this).text().trim() === 'Actions') {
            th.html('');
        } else {
            th.html('<input type="text" class="form-control form-control-sm" placeholder="Search">');
        }
        filterRow.append(th);
    });
    thead.append(filterRow);
    const dataTable = table.DataTable({ dom: 'lrtip', orderCellsTop:true, fixedHeader:true, pageLength:10, lengthMenu:[10,25,50,100], responsive:true, columnDefs:[{orderable:false,targets:[0,-1]}] });
    table.find('thead').on('keyup change', 'tr.filters input', function(){ const idx=$(this).closest('th').index(); const val=$(this).val(); if (dataTable.column(idx).search()!==val) dataTable.column(idx).search(val).draw(); });
    return dataTable;
}

function showAddModal(modalId, formId, opts = {}) {
    const form = document.getElementById(formId);
    if (form) form.reset();
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

// Utility: fetch JSON safe
async function fetchJson(url, opts) {
    const res = await fetch(url, opts);
    return res.json();
}

document.addEventListener('DOMContentLoaded', function () {
    // Attach handlers for select-all and row selection for any table with .data-table
    document.querySelectorAll('table.data-table').forEach(function (table) {
        const tableId = table.id || '';
        if (!tableId) return;
        const selectAll = document.getElementById('select-all-' + tableId.replace('-table', '')) || document.getElementById('select-all-' + tableId);
        // If header checkbox not found by that id, try generic select-all for table id
        const headerCheckbox = table.querySelector('thead input[type="checkbox"]');
        const selectAllEl = selectAll || headerCheckbox;

        // Add row checkbox behavior
        const updateDeleteButton = function () {
            const checked = table.querySelectorAll('tbody input.row-select:checked');
            const count = checked.length;
            const pageName = tableId.replace('-table', '');
            const deleteBtn = document.getElementById('delete-selected-' + pageName);
            if (deleteBtn) {
                deleteBtn.style.display = count > 0 ? 'inline-block' : 'none';
                deleteBtn.dataset.count = count;
                deleteBtn.textContent = count > 0 ? `Delete Selected (${count})` : 'Delete Selected';
            }
        };

        // Wire select-all
        if (selectAllEl) {
            selectAllEl.addEventListener('change', function (e) {
                const checked = !!e.target.checked;
                table.querySelectorAll('tbody input.row-select').forEach(function (cb) {
                    cb.checked = checked;
                });
                updateDeleteButton();
            });
        }

        // Use event delegation for row checkboxes to survive DataTables redraws
        table.querySelector('tbody').addEventListener('change', function (e) {
            const target = e.target;
            if (!target || !target.matches('input.row-select')) return;
            const all = table.querySelectorAll('tbody input.row-select');
            const checked = table.querySelectorAll('tbody input.row-select:checked');
            if (selectAllEl) selectAllEl.checked = (all.length > 0 && checked.length === all.length);
            updateDeleteButton();
        });

        // Wire delete button
        const pageName = tableId.replace('-table', '');
        const deleteBtn = document.getElementById('delete-selected-' + pageName);
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async function () {
                const selected = Array.from(table.querySelectorAll('tbody input.row-select:checked')).map(i => i.dataset.id).filter(Boolean);
                if (!selected.length) return;
                if (!confirm(`Delete ${selected.length} selected items? This cannot be undone.`)) return;
                // Show loading overlay if CRUD helper exists
                if (window.CRUD && typeof CRUD.showLoading === 'function') CRUD.showLoading('tableContainer');
                try {
                    // Delete sequentially using existing single-delete endpoints: api/<pageName>.php?action=delete
                    for (const id of selected) {
                        const params = new URLSearchParams();
                        params.append('id', id);
                        // try POST via fetch
                        await fetch(`api/${pageName}.php?action=delete`, { method: 'POST', body: params });
                    }
                    // reload after deletes
                    window.location.reload();
                } catch (e) {
                    alert('Batch delete failed: ' + (e.message || e));
                } finally {
                    if (window.CRUD && typeof CRUD.hideLoading === 'function') CRUD.hideLoading();
                }
            });
        }
    });
});

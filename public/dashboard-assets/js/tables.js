/* ============================================
   📊 UNIVERSAL DASHBOARD TABLE SCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

    // 🔍 Search menu toggle
    const searchBtn = document.getElementById('searchOptionsButton');
    const menu = document.getElementById('searchOptionsMenu');

    if (searchBtn && menu) {
        searchBtn.addEventListener('click', e => {
            e.stopPropagation();
            menu.classList.toggle('show-search-menu');
        });

        document.addEventListener('click', e => {
            if (!searchBtn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show-search-menu');
            }
        });
    }

    // 🔢 Table Sorting
    const headers = document.querySelectorAll('.header-cell.sortable');

    headers.forEach((header, index) => {
        header.addEventListener('click', () => {
            const table = document.getElementById('dataTable');
            const tbody = table.querySelector('tbody');

            // Determine sort direction
            const isAsc = header.classList.contains('sort-asc');
            const direction = isAsc ? 'desc' : 'asc';

            // Remove existing sort classes from all headers
            headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

            // Add class to current header
            header.classList.add('sort-' + direction);

            // Sort rows
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort((a, b) => {
                const aText = a.cells[index].innerText.trim().toUpperCase();
                const bText = b.cells[index].innerText.trim().toUpperCase();
                return direction === 'asc'
                    ? aText.localeCompare(bText)
                    : bText.localeCompare(aText);
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // ✅ Select All / Toggle
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            document.querySelectorAll('.select-booking').forEach(cb => cb.checked = this.checked);
        });
    }

    // 🔎 Live Search / Filter
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    // Reset Button (if exists)
    const resetBtn = document.getElementById('resetSearch');
    if (resetBtn) {
        resetBtn.addEventListener('click', clearSearchAndReset);
    }
});

/* ============================================
   🔎 FILTER / SEARCH FUNCTION
   ============================================ */
function filterTable() {
    const input = document.getElementById('searchInput')?.value.toUpperCase() || '';
    const selectedColumn = document.querySelector('input[name="searchColumn"]:checked')?.value || 'all';
    const rows = document.querySelectorAll('#dataTable tbody tr');
    const headers = document.querySelectorAll('.header-cell');

    rows.forEach(row => {
        let match = false;
        if (selectedColumn === 'all') {
            // Search in all columns
            match = [...row.querySelectorAll('td')].some(td =>
                td.innerText.toUpperCase().includes(input)
            );
        } else {
            // Search in specific column
            const headerIndex = Array.from(headers).findIndex(
                h => h.dataset.column === selectedColumn
            );
            if (headerIndex > -1) {
                const cell = row.cells[headerIndex];
                match = cell ? cell.innerText.toUpperCase().includes(input) : false;
            }
        }
        row.style.display = match ? '' : 'none';
    });
}

/* ============================================
   🔄 RESET FUNCTION
   ============================================ */
function clearSearchAndReset() {
    const input = document.getElementById('searchInput');
    if (input) input.value = '';

    const allColumn = document.querySelector('input[name="searchColumn"][value="all"]');
    if (allColumn) allColumn.checked = true;

    filterTable();
}

/* ============================================
   ✅ TOGGLE SELECT ALL FUNCTION (optional manual call)
   ============================================ */
function toggleSelectAll(source) {
    document.querySelectorAll('.select-booking').forEach(cb => cb.checked = source.checked);
}

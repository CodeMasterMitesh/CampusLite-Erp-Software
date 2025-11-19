// public/assets/js/export-print.js
function exportTable(tableId) {
    var wb = XLSX.utils.table_to_book(document.getElementById(tableId), {sheet: "Sheet1"});
    XLSX.writeFile(wb, tableId + ".xlsx");
}
function printTable(tableId) {
    var printContents = document.getElementById(tableId).outerHTML;
    var win = window.open('', '', 'height=600,width=800');
    win.document.write('<html><head><title>Print Table</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    win.document.write('</head><body>');
    win.document.write(printContents);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}

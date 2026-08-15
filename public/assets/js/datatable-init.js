// (function () {
//     function buildOptions(table) {
//         const exportName = table.dataset.exportName || 'export';
//         const pageLength = parseInt(table.dataset.pageLength) || 25;
//         const useFixedColumns = table.dataset.fixedColumns !== undefined;
//         const options = {
//             // IMPORTANT:
//             // Disable Responsive so DataTables does not hide columns
//             // or create the arrow/expand button.
//             responsive: false,
//             select: false,
//             pageLength: pageLength,
//             // Records per page
//             lengthMenu: [
//                 [10, 25, 50, 100, -1],
//                 [10, 25, 50, 100, 'All']
//             ],
//             order: [],
//             // l = length selector
//             // B = buttons
//             // f = search
//             // i = information
//             // p = pagination
//             dom:
//                 "<'row mb-3 align-items-center'" +
//                     "<'col-md-4'l>" +
//                     "<'col-md-4'B>" +
//                     "<'col-md-4'f>" +
//                 ">" +
//                 "rt" +
//                 "<'row mt-3 align-items-center'" +
//                     "<'col-md-6'i>" +
//                     "<'col-md-6'p>" +
//                 ">",

//             buttons: [

//                  {
//                     extend: 'pdfHtml5',
//                     text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
//                     className: 'btn btn-danger btn-sm',
//                     title: exportName,
//                     orientation: 'landscape'
//                 },
//                 {
//                     extend: 'excelHtml5',
//                     text: '<i class="bi bi-file-earmark-excel"></i> Excel',
//                     className: 'btn btn-info btn-sm',
//                     title: exportName
//                 },
//                 {
//                     extend: 'csvHtml5',
//                     text: '<i class="bi bi-filetype-csv"></i> CSV',
//                     className: 'btn btn-success btn-sm',
//                     title: exportName
//                 }

//             ]
//         };

//         if (useFixedColumns) {

//             options.scrollX = true;

//             options.fixedColumns = {
//                 leftColumns: 1
//             };
//         }

//         return options;
//     }

//     function initTable(table) {
//         if (! table || ! table.querySelector('thead')) return; // DataTables requires a <thead>

//         const $table = $(table);

//         if ($.fn.DataTable.isDataTable(table)) {
//             $table.DataTable().destroy();
//         }

//         $table.DataTable(buildOptions(table));
//     }

//     function initAll(root) {
//         (root || document).querySelectorAll('table[data-datatable]').forEach(initTable);
//     }

//     window.DataTableInit = { initTable, initAll };

//     document.addEventListener('DOMContentLoaded', () => initAll(document));
// })();

(function () {
    function buildOptions(table) {
        const exportName = table.dataset.exportName || 'export';
        const pageLength = parseInt(table.dataset.pageLength) || 25;
        const useFixedColumns = table.dataset.fixedColumns !== undefined;
        const exportColumns = ':visible:not(.no-export)';
        const exportColumnCount = table.querySelectorAll('thead th:not(.no-export)').length;
        const pdfOrientation = table.dataset.pdfOrientation || (exportColumnCount > 6 ? 'landscape' : 'portrait');
        
        const options = {
            responsive: false,
            select: false,
            pageLength: pageLength,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            order: [],
            dom:
                "<'row mb-3 align-items-center'" +
                    "<'col-md-4'l>" +
                    "<'col-md-4 text-center'B>" + // Added text-center to align buttons nicely
                    "<'col-md-4'f>" +
                ">" +
                "rt" +
                "<'row mt-3 align-items-center'" +
                    "<'col-md-6'i>" +
                    "<'col-md-6'p>" +
                ">",

            buttons: [
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm me-2',
                title: exportName,
                orientation: pdfOrientation,
                pageSize: 'A4',
                exportOptions: {
                    columns: exportColumns
                },
                customize: function (doc) {
                    // 1. Center and format Document Title
                    doc.styles.title = {
                        color: '#212529',
                        fontSize: 16,
                        bold: true,
                        alignment: 'center',
                        margin: [0, 0, 0, 15]
                    };

                    // 2. Base typography
                    doc.defaultStyle.fontSize = 9;
                    doc.styles.tableHeader = {
                        fontSize: 10,
                        bold: true,
                        color: '#000000',
                        fillColor: '#f8f9fa',
                        alignment: 'left'
                    };

                    // 3. Dynamic Table Configuration
                    const tableNode = doc.content.find(item => item.table);
                    if (tableNode) {
                        const body = tableNode.table.body;
                        const columnCount = body[0].length; // Detect actual exported columns

                        // DYNAMIC WIDTHS:
                        // If exactly 5 columns match your summary table, use proportional percentages.
                        // Otherwise, distribute space evenly ('*') across all columns to prevent errors.
                        if (columnCount === 5) {
                            tableNode.table.widths = ['8%', '42%', '18%', '16%', '16%'];
                        } else {
                            tableNode.table.widths = Array(columnCount).fill('*');
                        }

                        // Apply borders & padding safely
                        tableNode.layout = {
                            hLineWidth: () => 0.5,
                            vLineWidth: () => 0.5,
                            hLineColor: () => '#dee2e6',
                            vLineColor: () => '#dee2e6',
                            paddingLeft: () => 8,
                            paddingRight: () => 8,
                            paddingTop: () => 6,
                            paddingBottom: () => 6
                        };

                        // Safe cell alignment loop based on actual table dimensions
                        for (let rowIndex = 0; rowIndex < body.length; rowIndex++) {
                            const row = body[rowIndex];

                            // Center S/N (first column) and UoM (third column) if they exist
                            if (row[0]) row[0].alignment = 'center';
                            if (columnCount >= 3 && row[2]) row[2].alignment = 'center';

                            // Right-align numeric totals on data rows
                            if (rowIndex > 0 && columnCount >= 5 && row[4]) {
                                row[4].alignment = 'right';
                            }
                        }
                    }
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                className: 'btn btn-info btn-sm me-2',
                title: exportName,
                exportOptions: {
                    columns: exportColumns
                },
                customize: function (xlsx) {
                    const sheet = xlsx.xl.worksheets['sheet1.xml'];

                    // DataTables provides built-in style IDs for Excel XML:
                    // 25 = Thin border around cells
                    // 32 = Bold text with thin border (ideal for headers)
                    // 51 = Centered text with thin border

                    // Apply borders to all table cells
                    $('row c', sheet).attr('s', '25');

                    // Apply bold font and borders to the header row
                    $('row:first c', sheet).attr('s', '32');
                    
                    // Apply header height (optional)
                    $('row:first', sheet).attr('ht', '25').attr('customHeight', '1');
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="bi bi-filetype-csv"></i> CSV',
                className: 'btn btn-success btn-sm',
                title: exportName,
                exportOptions: {
                    columns: exportColumns
                }
            }
        ]
        };
        if (useFixedColumns) {
            options.scrollX = true;
            options.fixedColumns = {
                leftColumns: 1
            };
        }
        return options;
    }

    function initTable(table) {
        if (!table || !table.querySelector('thead')) return; 
        const $table = $(table);
        $table.addClass('table table-bordered');
        if ($.fn.DataTable.isDataTable(table)) {
            $table.DataTable().destroy();
        }
        $table.DataTable(buildOptions(table));
    }
    function initAll(root) {
        (root || document).querySelectorAll('table[data-datatable]').forEach(initTable);
    }
    window.DataTableInit = { initTable, initAll };
    document.addEventListener('DOMContentLoaded', () => initAll(document));
})();

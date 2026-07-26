$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let lines = window.storeOrderingExistingItems || [];

    function renderLines() {
        const $body = $('#requisitionItemsTable tbody').empty();

        if (! lines.length) {
            $body.append('<tr class="js-empty-row"><td colspan="8" class="text-center text-muted">No data available</td></tr>');
            return;
        }

        lines.forEach((line, i) => {
            $body.append(`
                <tr data-index="${i}">
                    <td>${i + 1}</td>
                    <td>${line.name}</td>
                    <td>—</td>
                    <td><input type="number" min="1" class="form-control form-control-sm js-line-units" value="${line.units}"></td>
                    <td><input type="number" min="1" class="form-control form-control-sm js-line-per-unit" value="${line.itemsPerUnit}"></td>
                    <td class="js-line-quantity">${line.units * line.itemsPerUnit}</td>
                    <td><input type="text" class="form-control form-control-sm js-line-details" value="${line.details || ''}"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-line">✕</button></td>
                </tr>
            `);
        });
    }

    renderLines();

    document.addEventListener('item-picker:add', function (e) {
        if (lines.some(l => l.id == e.detail.id)) {
            Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${e.detail.name}" is already in this order.` });
            return;
        }

        lines.push({ id: e.detail.id, name: e.detail.name, units: 1, itemsPerUnit: 1, details: '' });
        renderLines();
    });

    $('#requisitionItemsTable').on('input', '.js-line-units, .js-line-per-unit, .js-line-details', function () {
        const i = $(this).closest('tr').data('index');
        const $row = $('#requisitionItemsTable tbody tr').eq(i);

        const unitsRaw = $row.find('.js-line-units').val();
        const perUnitRaw = $row.find('.js-line-per-unit').val();

        lines[i].units = unitsRaw === '' ? '' : parseInt(unitsRaw);
        lines[i].itemsPerUnit = perUnitRaw === '' ? '' : parseInt(perUnitRaw);
        lines[i].details = $row.find('.js-line-details').val();

        const previewUnits = lines[i].units || 0;
        const previewPerUnit = lines[i].itemsPerUnit || 0;
        $row.find('.js-line-quantity').text(previewUnits * previewPerUnit);
    });

    $('#requisitionItemsTable').on('blur', '.js-line-units, .js-line-per-unit', function () {
        const i = $(this).closest('tr').data('index');
        const $row = $('#requisitionItemsTable tbody tr').eq(i);

        let units = parseInt($row.find('.js-line-units').val());
        let perUnit = parseInt($row.find('.js-line-per-unit').val());

        if (! units || units < 1) units = 1;
        if (! perUnit || perUnit < 1) perUnit = 1;

        $row.find('.js-line-units').val(units);
        $row.find('.js-line-per-unit').val(perUnit);

        lines[i].units = units;
        lines[i].itemsPerUnit = perUnit;
        $row.find('.js-line-quantity').text(units * perUnit);
    });

    $('#requisitionItemsTable').on('click', '.js-remove-line', function () {
        const i = $(this).closest('tr').data('index');
        const name = lines[i].name;

        Swal.fire({
            icon: 'warning',
            title: 'Remove item?',
            text: `Remove "${name}" from this order?`,
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel',
        }).then(result => {
            if (result.isConfirmed) {
                lines.splice(i, 1);
                renderLines();
            }
        });
    });

    $('#js-save-items').on('click', function () {
        document.activeElement.blur();

        if (! lines.length) {
            Swal.fire({ icon: 'warning', title: 'No items', text: 'An order must have at least one item.' });
            return;
        }

        $.ajax({
            url: window.storeOrderingEditRoutes.update,
            method: 'POST',
            data: {
                items: lines.map(l => ({
                    item_id: l.id,
                    units: l.units,
                    items_per_unit: l.itemsPerUnit,
                    item_details: l.details,
                })),
            },
        })
            .done(() => {
                Swal.fire({ icon: 'success', title: 'Items updated', timer: 1200, showConfirmButton: false })
                    .then(() => { window.location.href = window.storeOrderingEditRoutes.pendingOrder; });
            })
            .fail(xhr => Swal.fire({ icon: 'error', title: 'Update failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
    });
});
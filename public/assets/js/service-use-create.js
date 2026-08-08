(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#storeLabel').val($('.settings-panel-head strong').first().parent().text().replace('Sub Department', '').trim());

        let lines = [];
        let searchTimer;

        $('#js-lock-header').on('click', function () {
            const requisitionDate = $('#requisitionDate').val();
            const reason = $('#reason').val().trim();

            if (! requisitionDate || ! reason) {
                Swal.fire({ icon: 'warning', title: 'Missing fields', text: 'Please provide Requisition Date and Reason before continuing.' });
                return;
            }

            $('#requisitionDate, #reason, #js-lock-header').prop('disabled', true);
            $('#itemSelectionSection').removeClass('d-none');
        });

        function searchPicker() {
            const categoryId = $('#suPickerCategory').val();
            const term = $('#suPickerSearch').val();

            if (! categoryId && ! term) {
                $('#suPickerResults').html('<tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>');
                return;
            }

            $.get(window.serviceUseRoutes.itemsPicker, { category_id: categoryId, search: term }).done(items => {
                const $tbody = $('#suPickerResults').empty();

                if (! items.length) {
                    $tbody.html('<tr><td colspan="2" class="text-muted text-center">No items with stock found</td></tr>');
                    return;
                }

                items.forEach(item => {
                    $tbody.append(`
                        <tr class="js-su-picker-row" style="cursor:pointer" data-id="${item.id}" data-name="${item.name}" data-uom="${item.uom}" data-balance="${item.balance}">
                            <td>${item.name}</td><td>${item.balance}</td>
                        </tr>
                    `);
                });
            });
        }

        $('#suPickerCategory').on('change', searchPicker);
        $('#suPickerSearch').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(searchPicker, 300);
        });

        $('#suPickerResults').on('click', '.js-su-picker-row', function () {
            const id = $(this).data('id');

            if (lines.some(l => l.id === id)) {
                Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${$(this).data('name')}" is already added.` });
                return;
            }

            lines.push({
                id: id,
                name: $(this).data('name'),
                uom: $(this).data('uom') || '—',
                balance: parseInt($(this).data('balance')) || 0,
                quantity: 1,
            });

            renderLines();
        });

        function renderLines() {
            const $body = $('#serviceUseItemsTable tbody').empty();

            if (! lines.length) {
                $body.append('<tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No items added</td></tr>');
                return;
            }

            lines.forEach((line, i) => {
                $body.append(`
                    <tr data-index="${i}">
                        <td>${i + 1}</td>
                        <td>${line.name}</td>
                        <td>${line.uom}</td>
                        <td>${line.balance}</td>
                        <td><input type="number" min="1" max="${line.balance}" class="form-control form-control-sm js-su-qty" value="${line.quantity}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-su-remove">✕</button></td>
                    </tr>
                `);
            });
        }

        $('#serviceUseItemsTable').on('input', '.js-su-qty', function () {
            const i = $(this).closest('tr').data('index');
            const raw = $(this).val();
            lines[i].quantity = raw === '' ? '' : parseInt(raw);
        });

        $('#serviceUseItemsTable').on('blur', '.js-su-qty', function () {
            const i = $(this).closest('tr').data('index');
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;
            if (qty > lines[i].balance) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Only ${lines[i].balance} unit(s) of "${lines[i].name}" are available.` });
                qty = lines[i].balance;
            }
            $(this).val(qty);
            lines[i].quantity = qty;
        });

        $('#serviceUseItemsTable').on('click', '.js-su-remove', function () {
            const i = $(this).closest('tr').data('index');
            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                lines.splice(i, 1);
                renderLines();
            });
        });

        $('#js-cancel-service-use').on('click', function () {
            window.location.href = window.serviceUseRoutes.previousList;
        });

        $('#js-submit-service-use').on('click', function () {
            document.activeElement.blur();

            if (! lines.length) {
                Swal.fire({ icon: 'warning', title: 'No items added', text: 'Add at least one item before submitting.' });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Confirm dispensing?',
                text: 'This will immediately deduct the selected quantities from store stock. This cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Yes, dispense',
            }).then(result => {
                if (! result.isConfirmed) return;

                $.post(window.serviceUseRoutes.store, {
                    requisition_date: $('#requisitionDate').val(),
                    reason: $('#reason').val(),
                    items: lines.map(l => ({ item_id: l.id, quantity: l.quantity })),
                })
                    .done(() => {
                        Swal.fire({ icon: 'success', title: 'Dispensed', timer: 1200, showConfirmButton: false })
                            .then(() => { window.location.href = window.serviceUseRoutes.previousList; });
                    })
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
            });
        });
    });
});
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let lines = window.storeTransferExistingLines || [];

        function renderLines() {
            const $body = $('#transferItemsTable tbody').empty();

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
                        <td><input type="number" min="1" max="${line.balance}" class="form-control form-control-sm js-t-qty" value="${line.quantity}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-t-remove">✕</button></td>
                    </tr>
                `);
            });
        }

        document.addEventListener('item-picker:add', function (e) {
            if (lines.some(l => l.id === e.detail.id)) {
                Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${e.detail.name}" is already in this transfer.` });
                return;
            }

            lines.push({
                id: e.detail.id,
                name: e.detail.name,
                uom: e.detail.uom || '—',
                balance: e.detail.balance || 0,
                quantity: 1,
            });

            renderLines();
        });

        $('#transferItemsTable').on('input', '.js-t-qty', function () {
            const i = $(this).closest('tr').data('index');
            const raw = $(this).val();
            lines[i].quantity = raw === '' ? '' : parseInt(raw);
        });

        $('#transferItemsTable').on('blur', '.js-t-qty', function () {
            const i = $(this).closest('tr').data('index');
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;

            if (qty > lines[i].balance) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Only ${lines[i].balance} unit(s) of "${lines[i].name}" available.` });
                qty = lines[i].balance;
            }

            $(this).val(qty);
            lines[i].quantity = qty;
        });

        $('#transferItemsTable').on('click', '.js-t-remove', function () {
            const i = $(this).closest('tr').data('index');

            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                lines.splice(i, 1);
                renderLines();
            });
        });

        $('#js-save-draft').on('click', function () {
            document.activeElement.blur();

            if (! $('#toSubdepartmentId').val()) {
                Swal.fire({ icon: 'warning', title: 'Select a destination store' });
                return;
            }
            if (! lines.length) {
                Swal.fire({ icon: 'warning', title: 'No items added' });
                return;
            }

            $.post(window.storeTransferRoutes.update, {
                to_subdepartment_id: $('#toSubdepartmentId').val(),
                description: $('#description').val(),
                items: lines.map(l => ({ item_id: l.id, quantity: l.quantity })),
            })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.storeTransferRoutes.draftList; });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Save failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });

        renderLines();
    });
});
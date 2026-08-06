(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#storeLabel').val($('.settings-panel-head strong').first().parent().text().replace('Sub Department', '').trim());

        let supplierId = null;
        let lines = [];

        $('#js-lock-header').on('click', function () {
            const transactionDate = $('#transactionDate').val();
            const supId = $('#supplierId').val();
            const description = $('#description').val().trim();

            if (! transactionDate || ! supId || ! description) {
                Swal.fire({ icon: 'warning', title: 'Missing fields', text: 'Please provide Transaction Date, Description, and select a Receiving Supplier before continuing.' });
                return;
            }

            supplierId = supId;
            $('#transactionDate, #supplierId, #description, #js-lock-header').prop('disabled', true);
            $('#itemSelectionSection').removeClass('d-none');
        });

        function renderLines() {
            const $body = $('#returnItemsTable tbody').empty();

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
                        <td><input type="number" min="1" max="${line.balance}" class="form-control form-control-sm js-r-qty" value="${line.quantity}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-r-remove">✕</button></td>
                    </tr>
                `);
            });
        }

        document.addEventListener('item-picker:add', function (e) {
            if (lines.some(l => l.id === e.detail.id)) {
                Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${e.detail.name}" is already in this return.` });
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

        $('#returnItemsTable').on('input', '.js-r-qty', function () {
            const i = $(this).closest('tr').data('index');
            const raw = $(this).val();
            lines[i].quantity = raw === '' ? '' : parseInt(raw);
        });

        $('#returnItemsTable').on('blur', '.js-r-qty', function () {
            const i = $(this).closest('tr').data('index');
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;

            if (qty > lines[i].balance) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Only ${lines[i].balance} unit(s) of "${lines[i].name}" are available at this store.` });
                qty = lines[i].balance;
            }

            $(this).val(qty);
            lines[i].quantity = qty;
        });

        $('#returnItemsTable').on('click', '.js-r-remove', function () {
            const i = $(this).closest('tr').data('index');

            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                lines.splice(i, 1);
                renderLines();
            });
        });

        $('#js-cancel-return').on('click', function () {
            window.location.href = window.returnOutwardRoutes.draftList;
        });

        function savePayload() {
            document.activeElement.blur();

            if (! lines.length) {
                Swal.fire({ icon: 'warning', title: 'No items added', text: 'Add at least one item before saving.' });
                return Promise.reject();
            }

            return $.post(window.returnOutwardRoutes.store, {
                supplier_id: supplierId,
                transaction_date: $('#transactionDate').val(),
                description: $('#description').val(),
                items: lines.map(l => ({ item_id: l.id, quantity: l.quantity })),
            });
        }

        $('#js-save-return').on('click', function () {
            savePayload()
                .then(() => {
                    Swal.fire({ icon: 'success', title: 'Saved as draft', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.returnOutwardRoutes.draftList; });
                })
                .catch(xhr => {
                    if (! xhr) return;
                    Swal.fire({ icon: 'error', title: 'Save failed', text: xhr.responseJSON?.message || 'Something went wrong.' });
                });
        });

        $('#js-save-submit-return').on('click', function () {
            savePayload()
                .then(response => $.post(`/storage-supplies/return-outward/${response.id}/submit`))
                .then(() => {
                    Swal.fire({ icon: 'success', title: 'Submitted for approval', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.returnOutwardRoutes.draftList; });
                })
                .catch(xhr => {
                    if (! xhr) return;
                    Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' });
                });
        });
    });
});
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let issuingSubdepartmentId = window.requisitionExistingIssuingId;
        let lines = window.requisitionExistingLines || [];
        let searchTimer;

        function searchPicker() {
            const categoryId = $('#reqPickerCategory').val();
            const term = $('#reqPickerSearch').val();

            if (! categoryId && ! term) {
                $('#reqPickerResults').html('<tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>');
                return;
            }

            $.get(window.requisitionRoutes.itemsPicker, {
                issuing_subdepartment_id: issuingSubdepartmentId,
                category_id: categoryId,
                search: term,
            }).done(items => {
                const $tbody = $('#reqPickerResults').empty();

                if (! items.length) {
                    $tbody.html('<tr><td colspan="2" class="text-muted text-center">No items with stock found</td></tr>');
                    return;
                }

                items.forEach(item => {
                    $tbody.append(`
                        <tr class="js-req-picker-row" style="cursor:pointer" data-id="${item.id}" data-name="${item.name}" data-uom="${item.uom}" data-balance="${item.balance}">
                            <td>${item.name}</td><td>${item.balance}</td>
                        </tr>
                    `);
                });
            });
        }

        $('#reqPickerCategory').on('change', searchPicker);
        $('#reqPickerSearch').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(searchPicker, 300);
        });

        $('#reqPickerResults').on('click', '.js-req-picker-row', function () {
            const id = $(this).data('id');

            if (lines.some(l => l.id === id)) {
                Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${$(this).data('name')}" is already in this requisition.` });
                return;
            }

            lines.push({
                id: id,
                name: $(this).data('name'),
                uom: $(this).data('uom') || '—',
                balance: parseInt($(this).data('balance')) || 0,
                quantity: 1,
                details: '',
            });

            renderLines();
        });

        function renderLines() {
            const $body = $('#requisitionItemsTable tbody').empty();

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
                        <td><input type="number" min="1" max="${line.balance}" class="form-control form-control-sm js-req-qty" value="${line.quantity}"></td>
                        <td><input type="text" class="form-control form-control-sm js-req-details" value="${line.details || ''}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-req-remove">✕</button></td>
                    </tr>
                `);
            });
        }

        $('#requisitionItemsTable').on('input', '.js-req-qty', function () {
            const i = $(this).closest('tr').data('index');
            const raw = $(this).val();
            lines[i].quantity = raw === '' ? '' : parseInt(raw);
        });

        $('#requisitionItemsTable').on('input', '.js-req-details', function () {
            const i = $(this).closest('tr').data('index');
            lines[i].details = $(this).val();
        });

        $('#requisitionItemsTable').on('blur', '.js-req-qty', function () {
            const i = $(this).closest('tr').data('index');
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;

            if (qty > lines[i].balance) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Only ${lines[i].balance} unit(s) of "${lines[i].name}" are available at the Issuing Store.` });
                qty = lines[i].balance;
            }

            $(this).val(qty);
            lines[i].quantity = qty;
        });

        $('#requisitionItemsTable').on('click', '.js-req-remove', function () {
            const i = $(this).closest('tr').data('index');

            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                lines.splice(i, 1);
                renderLines();
            });
        });

        $('#js-save-requisition').on('click', function () {
            document.activeElement.blur();

            if (! lines.length) {
                Swal.fire({ icon: 'warning', title: 'No items added', text: 'Add at least one item before saving.' });
                return;
            }

            $.ajax({
                url: window.requisitionRoutes.update,
                method: 'POST',
                data: {
                    issuing_subdepartment_id: issuingSubdepartmentId,
                    description: $('#description').val(),
                    items: lines.map(l => ({ item_id: l.id, quantity_requested: l.quantity, item_details: l.details })),
                },
            })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.requisitionRoutes.pendingList; });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Save failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });

        renderLines();
    });
});
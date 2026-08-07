(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function todayLocal() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        function fmt(n) { return (Math.round(n * 100) / 100).toLocaleString('en-US'); }

        $('#storeLabel').val($('.settings-panel-head strong').first().parent().text().replace('Sub Department', '').trim());

        let reason = null;
        let addItems = [];   // { id, name, uom, batches: [] }
        let deductItems = []; // { id, name, uom, balance, quantity }
        let searchTimer;
        const batchModal = new bootstrap.Modal(document.getElementById('batchModal'));
        let currentAddIndex = null;

        $('#js-lock-header').on('click', function () {
            const adjustmentDate = $('#adjustmentDate').val();
            const description = $('#description').val().trim();
            const selectedReason = $('#reason').val();

            if (! adjustmentDate || ! description || ! selectedReason) {
                Swal.fire({ icon: 'warning', title: 'Missing fields', text: 'Please provide Adjustment Date, Description, and select an Adjustment Reason before continuing.' });
                return;
            }

            reason = selectedReason;
            $('#adjustmentDate, #description, #reason, #js-lock-header').prop('disabled', true);
            $('#itemSelectionSection').removeClass('d-none');

            if (reason === 'add_stock_balance') {
                $('#addItemsTable').removeClass('d-none');
                $('#deductItemsTable').addClass('d-none');
                $('#adjPickerBalanceHeader').text('Balance');
            } else {
                $('#deductItemsTable').removeClass('d-none');
                $('#addItemsTable').addClass('d-none');
                $('#adjPickerBalanceHeader').text('Store Balance');
            }
        });

        function searchPicker() {
            const categoryId = $('#adjPickerCategory').val();
            const term = $('#adjPickerSearch').val();

            if (! categoryId && ! term) {
                $('#adjPickerResults').html('<tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>');
                return;
            }

            $.get(window.stockAdjustmentRoutes.itemsPicker, { reason: reason, category_id: categoryId, search: term }).done(items => {
                const $tbody = $('#adjPickerResults').empty();

                if (! items.length) {
                    $tbody.html('<tr><td colspan="2" class="text-muted text-center">No items found</td></tr>');
                    return;
                }

                items.forEach(item => {
                    $tbody.append(`
                        <tr class="js-adj-picker-row" style="cursor:pointer" data-id="${item.id}" data-name="${item.name}" data-uom="${item.uom}" data-balance="${item.balance}">
                            <td>${item.name}</td><td>${item.balance}</td>
                        </tr>
                    `);
                });
            });
        }

        $('#adjPickerCategory').on('change', searchPicker);
        $('#adjPickerSearch').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(searchPicker, 300);
        });

        $('#adjPickerResults').on('click', '.js-adj-picker-row', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const uom = $(this).data('uom') || '—';
            const balance = parseInt($(this).data('balance')) || 0;

            if (reason === 'add_stock_balance') {
                if (addItems.some(i => i.id === id)) {
                    Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${name}" is already in this adjustment.` });
                    return;
                }
                addItems.push({ id, name, uom, batches: [] });
                renderAddItems();
            } else {
                if (deductItems.some(i => i.id === id)) {
                    Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${name}" is already in this adjustment.` });
                    return;
                }
                deductItems.push({ id, name, uom, balance, quantity: 1 });
                renderDeductItems();
            }
        });

        // ---- Add Stock Balance mode ----

        function renderAddItems() {
            const $body = $('#addItemsTable tbody').empty();

            if (! addItems.length) {
                $body.append('<tr class="js-empty-row"><td colspan="7" class="text-center text-muted">No items added</td></tr>');
                return;
            }

            addItems.forEach((item, i) => {
                const totalQty = item.batches.reduce((sum, b) => sum + b.quantity, 0);
                const totalAmount = item.batches.reduce((sum, b) => sum + (b.quantity * b.buying_price), 0);
                const btnClass = item.batches.length ? 'btn-success' : 'btn-dark';
                const btnLabel = item.batches.length ? `<i class="bi bi-check-circle"></i> ${item.batches.length} Batch${item.batches.length > 1 ? 'es' : ''}` : 'Batch';

                $body.append(`
                    <tr data-index="${i}">
                        <td>${i + 1}</td><td>${item.name}</td><td>${item.uom}</td>
                        <td>${totalQty}</td><td>${fmt(totalAmount)}</td>
                        <td><button type="button" class="btn btn-sm ${btnClass} js-open-add-batch">${btnLabel}</button></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-add-item">✕</button></td>
                    </tr>
                `);
            });
        }

        $('#addItemsTable').on('click', '.js-remove-add-item', function () {
            const i = $(this).closest('tr').data('index');
            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                addItems.splice(i, 1);
                renderAddItems();
            });
        });

        $('#addItemsTable').on('click', '.js-open-add-batch', function () {
            currentAddIndex = $(this).closest('tr').data('index');
            const item = addItems[currentAddIndex];

            $('#batchItemName').val(item.name);
            $('#batchNumber, #batchUnits, #batchManufactureDate, #batchExpiryDate').val('');
            $('#batchItemsPerUnit').val(1);
            $('#batchBuyingPrice').val('');
            $('#batchReceivedDate').val(todayLocal());
            $('#batchQuantityPreview').val(0);
            $('#batchFormError').text('');

            renderBatchList();
            batchModal.show();
        });

        $('#batchUnits, #batchItemsPerUnit').on('input', function () {
            const units = parseInt($('#batchUnits').val()) || 0;
            const perUnit = parseInt($('#batchItemsPerUnit').val()) || 0;
            $('#batchQuantityPreview').val(units * perUnit);
        });

        function renderBatchList() {
            const $tbody = $('#batchListTable tbody').empty();
            const batches = addItems[currentAddIndex].batches;

            if (! batches.length) {
                $tbody.append('<tr class="js-batch-empty-row"><td colspan="10" class="text-center text-muted">No data available</td></tr>');
                return;
            }

            batches.forEach((b, i) => {
                $tbody.append(`
                    <tr>
                        <td>${i + 1}</td><td>${b.batch_number}</td><td>${b.units}</td><td>${b.items_per_unit}</td>
                        <td>${b.quantity}</td><td>${fmt(b.buying_price)}</td><td>${b.manufacture_date}</td>
                        <td>${b.expiry_date}</td><td>${b.received_date}</td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-batch" data-index="${i}">Remove</button></td>
                    </tr>
                `);
            });
        }

        $('#js-add-batch-row').on('click', function () {
            const batchNumber = $('#batchNumber').val().trim();
            const units = parseInt($('#batchUnits').val());
            const itemsPerUnit = parseInt($('#batchItemsPerUnit').val());
            const buyingPrice = parseFloat($('#batchBuyingPrice').val());
            const manufactureDate = $('#batchManufactureDate').val();
            const expiryDate = $('#batchExpiryDate').val();
            const receivedDate = $('#batchReceivedDate').val();

            if (! batchNumber || ! units || units < 1 || ! itemsPerUnit || itemsPerUnit < 1 || isNaN(buyingPrice) || ! manufactureDate || ! expiryDate || ! receivedDate) {
                $('#batchFormError').text('Please fill in all required fields correctly.');
                return;
            }
            if (manufactureDate > todayLocal()) {
                $('#batchFormError').text('Manufacture Date cannot be greater than today.');
                return;
            }
            if (expiryDate < todayLocal()) {
                $('#batchFormError').text('Expiry Date cannot be earlier than today.');
                return;
            }
            $('#batchFormError').text('');

            addItems[currentAddIndex].batches.push({
                batch_number: batchNumber, units, items_per_unit: itemsPerUnit,
                quantity: units * itemsPerUnit, buying_price: buyingPrice,
                manufacture_date: manufactureDate, expiry_date: expiryDate, received_date: receivedDate,
            });

            renderBatchList();
            renderAddItems();

            $('#batchNumber, #batchUnits, #batchManufactureDate, #batchExpiryDate').val('');
            $('#batchItemsPerUnit').val(1);
            $('#batchQuantityPreview').val(0);
        });

        $('#batchListTable').on('click', '.js-remove-batch', function () {
            const index = $(this).data('index');
            Swal.fire({ icon: 'warning', title: 'Remove this batch?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                addItems[currentAddIndex].batches.splice(index, 1);
                renderBatchList();
                renderAddItems();
            });
        });

        // ---- Expired/Dump/Broken mode ----

        function renderDeductItems() {
            const $body = $('#deductItemsTable tbody').empty();

            if (! deductItems.length) {
                $body.append('<tr class="js-empty-row"><td colspan="6" class="text-center text-muted">No items added</td></tr>');
                return;
            }

            deductItems.forEach((line, i) => {
                $body.append(`
                    <tr data-index="${i}">
                        <td>${i + 1}</td><td>${line.name}</td><td>${line.uom}</td><td>${line.balance}</td>
                        <td><input type="number" min="1" max="${line.balance}" class="form-control form-control-sm js-d-qty" value="${line.quantity}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-deduct-item">✕</button></td>
                    </tr>
                `);
            });
        }

        $('#deductItemsTable').on('input', '.js-d-qty', function () {
            const i = $(this).closest('tr').data('index');
            const raw = $(this).val();
            deductItems[i].quantity = raw === '' ? '' : parseInt(raw);
        });

        $('#deductItemsTable').on('blur', '.js-d-qty', function () {
            const i = $(this).closest('tr').data('index');
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;
            if (qty > deductItems[i].balance) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Only ${deductItems[i].balance} unit(s) of "${deductItems[i].name}" are available.` });
                qty = deductItems[i].balance;
            }
            $(this).val(qty);
            deductItems[i].quantity = qty;
        });

        $('#deductItemsTable').on('click', '.js-remove-deduct-item', function () {
            const i = $(this).closest('tr').data('index');
            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                deductItems.splice(i, 1);
                renderDeductItems();
            });
        });

        // ---- Save ----

        $('#js-cancel-adjustment').on('click', function () {
            window.location.href = window.stockAdjustmentRoutes.draftList;
        });

        function buildPayload(submit) {
            document.activeElement.blur();

            if (reason === 'add_stock_balance') {
                if (! addItems.length || ! addItems.every(item => item.batches.length > 0)) {
                    Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Add at least one item, and give every item at least one batch.' });
                    return null;
                }
                return {
                    reason, adjustment_date: $('#adjustmentDate').val(), description: $('#description').val(), submit: submit ? 1 : 0,
                    items: addItems.map(item => ({ item_id: item.id, batches: item.batches })),
                };
            }

            if (! deductItems.length) {
                Swal.fire({ icon: 'warning', title: 'No items added', text: 'Add at least one item before saving.' });
                return null;
            }
            return {
                reason, adjustment_date: $('#adjustmentDate').val(), description: $('#description').val(), submit: submit ? 1 : 0,
                items: deductItems.map(l => ({ item_id: l.id, quantity: l.quantity })),
            };
        }

        function save(submit) {
            const payload = buildPayload(submit);
            if (! payload) return;

            $.post(window.stockAdjustmentRoutes.store, payload)
                .done(() => {
                    Swal.fire({ icon: 'success', title: submit ? 'Submitted for approval' : 'Saved as draft', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.stockAdjustmentRoutes.draftList; });
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    const firstError = errors ? Object.values(errors)[0]?.[0] : null;
                    Swal.fire({ icon: 'error', title: 'Save failed', text: firstError || xhr.responseJSON?.message || 'Something went wrong.' });
                });
        }

        $('#js-save-adjustment').on('click', () => save(false));
        $('#js-save-submit-adjustment').on('click', () => save(true));
    });
});
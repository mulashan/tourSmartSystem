(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        function todayLocal() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        const batchModal = new bootstrap.Modal(document.getElementById('batchModal'));
        let items = window.grnWithoutPoExistingItems || [];
        let currentIndex = null;

        function fmt(n) { return (Math.round(n * 100) / 100).toLocaleString('en-US'); }

        function renderItems() {
            const $body = $('#grnItemsTable tbody').empty();

            if (! items.length) {
                $body.append('<tr class="js-empty-row"><td colspan="8" class="text-center text-muted">No items added</td></tr>');
                return;
            }

            items.forEach((item, i) => {
                const totalQty = item.batches.reduce((sum, b) => sum + b.quantity, 0);
                const totalAmount = item.batches.reduce((sum, b) => sum + (b.quantity * b.buying_price), 0);
                const btnClass = item.batches.length ? 'btn-success' : 'btn-dark';
                const btnLabel = item.batches.length ? `<i class="bi bi-check-circle"></i> ${item.batches.length} Batch${item.batches.length > 1 ? 'es' : ''}` : 'Batch';

                $body.append(`
                    <tr data-index="${i}">
                        <td>${i + 1}</td>
                        <td>${item.name}</td>
                        <td>${item.uom}</td>
                        <td>${totalQty}</td>
                        <td>${fmt(totalAmount)}</td>
                        <td><input type="text" class="form-control form-control-sm js-item-remarks" value="${item.remarks || ''}"></td>
                        <td><button type="button" class="btn btn-sm ${btnClass} js-open-batch">${btnLabel}</button></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-item">✕</button></td>
                    </tr>
                `);
            });
        }

        document.addEventListener('item-picker:add', function (e) {
            if (items.some(i => i.id === e.detail.id)) {
                Swal.fire({ icon: 'warning', title: 'Item already added', text: `"${e.detail.name}" is already in this GRN.` });
                return;
            }

            items.push({ id: e.detail.id, name: e.detail.name, uom: e.detail.uom || '—', remarks: '', batches: [] });
            renderItems();
        });

        $('#grnItemsTable').on('input', '.js-item-remarks', function () {
            const i = $(this).closest('tr').data('index');
            items[i].remarks = $(this).val();
        });

        $('#grnItemsTable').on('click', '.js-remove-item', function () {
            const i = $(this).closest('tr').data('index');

            Swal.fire({ icon: 'warning', title: 'Remove this item?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                items.splice(i, 1);
                renderItems();
            });
        });

        $('#grnItemsTable').on('click', '.js-open-batch', function () {
            currentIndex = $(this).closest('tr').data('index');
            const item = items[currentIndex];

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
            const batches = items[currentIndex].batches;

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

            items[currentIndex].batches.push({
                batch_number: batchNumber, units, items_per_unit: itemsPerUnit,
                quantity: units * itemsPerUnit, buying_price: buyingPrice,
                manufacture_date: manufactureDate, expiry_date: expiryDate, received_date: receivedDate,
            });

            renderBatchList();
            renderItems();

            $('#batchNumber, #batchUnits, #batchManufactureDate, #batchExpiryDate').val('');
            $('#batchItemsPerUnit').val(1);
            $('#batchQuantityPreview').val(0);
        });

        $('#batchListTable').on('click', '.js-remove-batch', function () {
            const index = $(this).data('index');

            Swal.fire({ icon: 'warning', title: 'Remove this batch?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;
                items[currentIndex].batches.splice(index, 1);
                renderBatchList();
                renderItems();
            });
        });

        $('#js-submit-grn').on('click', function () {
            if (! items.length || ! items.every(item => item.batches.length > 0)) {
                Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Every item needs at least one batch before saving.' });
                return;
            }

            $.ajax({
                url: window.grnWithoutPoRoutes.update,
                method: 'POST',
                data: {
                    supplier_id: $('#supplierId').val(),
                    purchase_description: $('#purchaseDescription').val(),
                    delivery_note_number: $('#deliveryNoteNumber').val(),
                    invoice_number: $('#invoiceNumber').val(),
                    delivery_date: $('#deliveryDate').val(),
                    delivery_person: $('#deliveryPerson').val(),
                    vat_charges: $('#vatCharges').val(),
                    transport_charges: $('#transportCharges').val(),
                    labor_charges: $('#laborCharges').val(),
                    bank_charges: $('#bankCharges').val(),
                    freight_charges: $('#freightCharges').val(),
                    other_charges: $('#otherCharges').val(),
                    items: items.map(item => ({
                        item_id: item.id,
                        remarks: item.remarks,
                        batches: item.batches,
                    })),
                },
            })
                .done(response => {
                    console.log('GRN update confirmed by server:', response, '— client sent', items.length, 'items with', items.reduce((s, i) => s + i.batches.length, 0), 'total batches.');

                    Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.grnWithoutPoRoutes.pendingList; 
                            });
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    const firstError = errors ? Object.values(errors)[0]?.[0] : null;

                    Swal.fire({
                        icon: 'error',
                        title: 'Save failed',
                        text: firstError || xhr.responseJSON?.message || 'Something went wrong.',
                    });
                });
        });

        renderItems();
    });
});
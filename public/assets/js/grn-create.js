(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        function todayLocal() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }
        const batchModal = new bootstrap.Modal(document.getElementById('batchModal'));
        const batchData = {}; // { lpo_item_id: [ {batch_number, units, items_per_unit, quantity, buying_price, manufacture_date, expiry_date, received_date} ] }
        let currentLpoItemId = null;

        function fmt(n) { return (Math.round(n * 100) / 100).toLocaleString('en-US'); }

        function refreshRowTotals(lpoItemId) {
            const $row = $(`#grnItemsTable tr[data-lpo-item-id="${lpoItemId}"]`);
            const batches = batchData[lpoItemId] || [];
            const totalQty = batches.reduce((sum, b) => sum + b.quantity, 0);
            const price = parseFloat($row.data('price')) || 0;

            $row.find('.js-received-qty').text(totalQty);
            $row.find('.js-received-amount').text(fmt(totalQty * price));

            refreshBatchButtonState(lpoItemId);
            refreshSubmitButtonState();
        }

        $('#grnItemsTable').on('click', '.js-open-batch', function () {
            const missing = [];
            if (! $('#deliveryNoteNumber').val().trim()) missing.push('Delivery Note Number');
            if (! $('#invoiceNumber').val().trim()) missing.push('Invoice Number');
            if (! $('#deliveryDate').val()) missing.push('Delivery Date');

            if (missing.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Complete the GRN details first',
                    text: `Please fill in: ${missing.join(', ')} before adding batches.`,
                });
                return;
            }

            const $row = $(this).closest('tr');
            currentLpoItemId = $row.data('lpo-item-id');

            $('#batchItemName').val($row.data('item-name'));
            $('#batchQtyRequired').val($row.data('qty-required'));
            $('#batchNumber, #batchUnits, #batchManufactureDate, #batchExpiryDate').val('');
            $('#batchItemsPerUnit').val(1);
            $('#batchBuyingPrice').val($row.data('price'));
            $('#batchReceivedDate').val(todayLocal());
            $('#batchQuantityPreview').val(0);
            $('#batchFormError').text('');

            renderBatchList();
            updateRemaining();
            batchModal.show();
        });

        function refreshBatchButtonsState() {
            const complete = $('#deliveryNoteNumber').val().trim() && $('#invoiceNumber').val().trim() && $('#deliveryDate').val();
            $('.js-open-batch').prop('disabled', ! complete).toggleClass('disabled', ! complete);
        }

        $('#deliveryNoteNumber, #invoiceNumber, #deliveryDate').on('input change', refreshBatchButtonsState);
        refreshBatchButtonsState(); // run once on load, since Delivery Date is now pre-filled
        refreshSubmitButtonState();

        $('#batchUnits, #batchItemsPerUnit').on('input', function () {
            const units = parseInt($('#batchUnits').val()) || 0;
            const perUnit = parseInt($('#batchItemsPerUnit').val()) || 0;
            $('#batchQuantityPreview').val(units * perUnit);
        });

        function renderBatchList() {
            const $tbody = $('#batchListTable tbody').empty();
            const batches = batchData[currentLpoItemId] || [];

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

            const newQuantity = units * itemsPerUnit;
            const existingTotal = (batchData[currentLpoItemId] || []).reduce((sum, b) => sum + b.quantity, 0);
            const purchasedQty = parseInt($('#batchQtyRequired').val()) || 0;

            if (existingTotal + newQuantity > purchasedQty) {
                const remaining = purchasedQty - existingTotal;
                $('#batchFormError').text(`This batch (${newQuantity}) exceeds the remaining purchased quantity. Only ${remaining} unit(s) remain to be allocated.`);
                return;
            }

            $('#batchFormError').text('');

            if (! batchData[currentLpoItemId]) batchData[currentLpoItemId] = [];

            batchData[currentLpoItemId].push({
                batch_number: batchNumber, units, items_per_unit: itemsPerUnit,
                quantity: newQuantity, buying_price: buyingPrice,
                manufacture_date: manufactureDate, expiry_date: expiryDate, received_date: receivedDate,
            });

            renderBatchList();
            refreshRowTotals(currentLpoItemId);

            $('#batchNumber, #batchUnits, #batchManufactureDate, #batchExpiryDate').val('');
            $('#batchItemsPerUnit').val(1);
            $('#batchQuantityPreview').val(0);
        });

        $('#batchListTable').on('click', '.js-remove-batch', function () {
            const index = $(this).data('index');

            Swal.fire({ icon: 'warning', title: 'Remove this batch?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;

                batchData[currentLpoItemId].splice(index, 1);
                renderBatchList();
                refreshRowTotals(currentLpoItemId);
            });
        });

        $('#js-submit-inspection').on('click', function () {
            if (! refreshSubmitButtonState()) {
                Swal.fire({ icon: 'warning', title: 'Not ready yet', text: 'Complete the GRN details and fully allocate batches for every item before submitting.' });
                return;
            }

            const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

            const items = [];
            let mismatch = null;

            $('#grnItemsTable tbody tr').each(function () {
                const lpoItemId = $(this).data('lpo-item-id');
                const qtyRequired = $(this).data('qty-required');
                const batches = batchData[lpoItemId] || [];
                const total = batches.reduce((sum, b) => sum + b.quantity, 0);

                if (! batches.length || total !== qtyRequired) {
                    mismatch = $(this).data('item-name');
                    return false;
                }

                items.push({
                    lpo_item_id: lpoItemId,
                    remarks: $(this).find('.js-item-remarks').val(),
                    batches: batches,
                });
            });

            if (mismatch) {
                Swal.fire({ icon: 'warning', title: 'Batch mismatch', text: `"${mismatch}" needs batches whose total quantity exactly matches the purchased quantity before you can submit.` });
                return;
            }

            const formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('delivery_note_number', $('#deliveryNoteNumber').val());
            formData.append('invoice_number', $('#invoiceNumber').val());
            formData.append('delivery_date', $('#deliveryDate').val());
            formData.append('delivery_person', $('#deliveryPerson').val());

            if ($('#deliveryNoteAttachment')[0].files[0]) formData.append('delivery_note_attachment', $('#deliveryNoteAttachment')[0].files[0]);
            if ($('#invoiceAttachment')[0].files[0]) formData.append('invoice_attachment', $('#invoiceAttachment')[0].files[0]);

            items.forEach((item, i) => {
                formData.append(`items[${i}][lpo_item_id]`, item.lpo_item_id);
                formData.append(`items[${i}][remarks]`, item.remarks || '');
                item.batches.forEach((b, j) => {
                    Object.keys(b).forEach(key => formData.append(`items[${i}][batches][${j}][${key}]`, b[key]));
                });
            });

            $.ajax({
                url: window.grnRoutes.store,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            })
                .done(response => {
                    $.post(`/storage-supplies/grn/${response.id}/submit`)
                        .done(() => {
                            Swal.fire({ icon: 'success', title: 'Submitted for inspection', timer: 1200, showConfirmButton: false })
                                .then(() => { window.location.href = window.grnRoutes.listIndex; });
                        })
                        .fail(xhr => Swal.fire({ icon: 'error', title: 'Submit failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
                })
               $btn.prop('disabled', false).html('Submit for Approval');
               Swal.fire({ icon: 'error', title: 'Submit failed', text: xhr.responseJSON?.message || 'Something went wrong.' });
        });

        function updateRemaining() {
            const purchasedQty = parseInt($('#batchQtyRequired').val()) || 0;
            const total = (batchData[currentLpoItemId] || []).reduce((sum, b) => sum + b.quantity, 0);
            $('#batchRemaining').val(purchasedQty - total);
        }

        function refreshBatchButtonState(lpoItemId) {
            const $btn = $(`#grnItemsTable tr[data-lpo-item-id="${lpoItemId}"] .js-open-batch`);
            const $row = $btn.closest('tr');
            const batches = batchData[lpoItemId] || [];
            const purchasedQty = parseInt($row.data('qty-required')) || 0;
            const totalQty = batches.reduce((sum, b) => sum + b.quantity, 0);

            $btn.removeClass('btn-dark btn-warning btn-success');

            if (! batches.length) {
                $btn.addClass('btn-dark').html('Batch');
                return;
            }

            const remaining = purchasedQty - totalQty;
            const batchLabel = `${batches.length} Batch${batches.length > 1 ? 'es' : ''}`;

            if (remaining > 0) {
                $btn.addClass('btn-warning').html(`<i class="bi bi-exclamation-triangle"></i> ${batchLabel} &middot; ${remaining} left`);
            } else {
                $btn.addClass('btn-success').html(`<i class="bi bi-check-circle"></i> ${batchLabel} &middot; Complete`);
            }
        }

        function refreshSubmitButtonState() {
            const headerComplete = $('#deliveryNoteNumber').val().trim() && $('#invoiceNumber').val().trim() && $('#deliveryDate').val();

            let allItemsComplete = true;

            $('#grnItemsTable tbody tr').each(function () {
                const lpoItemId = $(this).data('lpo-item-id');
                const purchasedQty = parseInt($(this).data('qty-required')) || 0;
                const batches = batchData[lpoItemId] || [];
                const totalQty = batches.reduce((sum, b) => sum + b.quantity, 0);

                if (! batches.length || totalQty !== purchasedQty) {
                    allItemsComplete = false;
                }
            });

            const ready = headerComplete && allItemsComplete;
            $('#js-submit-inspection').prop('disabled', ! ready);
            return ready;
        }
    });
});
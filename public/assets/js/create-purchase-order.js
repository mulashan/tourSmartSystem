$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function formatMoney(n) {
        return (Math.round(n * 100) / 100).toLocaleString('en-US');
    }

    function recalcRow($row) {
        const units = parseInt($row.find('.js-po-units').val()) || 0;
        const perUnit = parseInt($row.find('.js-po-per-unit').val()) || 0;
        const price = parseFloat($row.find('.js-po-price').val()) || 0;
        const quantity = units * perUnit;

        $row.find('.js-po-quantity').text(quantity);
        $row.find('.js-po-total').text(formatMoney(quantity * price));
    }

    function recalcSummary() {
        let itemsTotal = 0;

        $('#poItemsTable tbody tr').each(function () {
            if (! $(this).find('.js-po-include').is(':checked')) return;

            const quantity = parseInt($(this).find('.js-po-quantity').text()) || 0;
            const price = parseFloat($(this).find('.js-po-price').val()) || 0;
            itemsTotal += quantity * price;
        });

        const vat = parseFloat($('#vatCharges').val()) || 0;
        const otherSum = ['#transportCharges', '#laborCharges', '#bankCharges', '#freightCharges', '#otherCharges']
            .reduce((sum, id) => sum + (parseFloat($(id).val()) || 0), 0);

        $('#js-po-grand-items-total').text(formatMoney(itemsTotal));
        $('#js-summary-other').text(formatMoney(otherSum));
        $('#js-summary-vat').text(formatMoney(vat));
        $('#js-summary-grand-total').text(formatMoney(itemsTotal + otherSum + vat));
    }

    $('#poItemsTable').on('input', '.js-po-units, .js-po-per-unit, .js-po-price', function () {
        recalcRow($(this).closest('tr'));
        recalcSummary();
    });

    $('#poItemsTable').on('change', '.js-po-include', recalcSummary);

    $('#vatCharges, #transportCharges, #laborCharges, #bankCharges, #freightCharges, #otherCharges').on('input', recalcSummary);

    $('#js-select-all').on('change', function () {
        $('.js-po-include').prop('checked', $(this).is(':checked'));
        recalcSummary();
    });

    $('#currencyType').on('change', function () {
        $('.js-currency-label, #currencyLabel').text($(this).val());
    });

    $('#poItemsTable tbody tr').each(function () { recalcRow($(this)); });
    recalcSummary();

    $('#js-save-draft').on('click', function () {
        if (! $('#requisitionDescription').val().trim()) {
            Swal.fire({ icon: 'warning', title: 'Requisition Description required' });
            return;
        }

        const items = [];
        const rejectedItemIds = [];
        let hasInvalidPrice = false;

        $('#poItemsTable tbody tr').each(function () {
            const $row = $(this);
            const requisitionItemId = $row.data('requisition-item-id');

            if (! $row.find('.js-po-include').is(':checked')) {
                rejectedItemIds.push(requisitionItemId);
                return;
            }

            const price = parseFloat($row.find('.js-po-price').val());
            if (! price || price <= 0) hasInvalidPrice = true;

            items.push({
                requisition_item_id: requisitionItemId,
                units: parseInt($row.find('.js-po-units').val()) || 1,
                items_per_unit: parseInt($row.find('.js-po-per-unit').val()) || 1,
                price: price || 0,
            });
        });

        if (! items.length) {
            Swal.fire({ icon: 'warning', title: 'No items included', text: 'At least one item must be included to save a Purchase Order.' });
            return;
        }

        if (hasInvalidPrice) {
            Swal.fire({ icon: 'warning', title: 'Missing prices', text: 'Every included item needs a price greater than zero.' });
            return;
        }

        $.ajax({
            url: window.procurementRoutes.storePo,
            method: 'POST',
            data: {
                supplier_id: $('#supplierId').val(),
                currency_type: $('#currencyType').val(),
                requisition_description: $('#requisitionDescription').val(),
                vat_charges: $('#vatCharges').val(),
                transport_charges: $('#transportCharges').val(),
                labor_charges: $('#laborCharges').val(),
                bank_charges: $('#bankCharges').val(),
                freight_charges: $('#freightCharges').val(),
                other_charges: $('#otherCharges').val(),
                items: items,
                rejected_item_ids: rejectedItemIds,
            },
        })
            .done(() => {
                Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false })
                    .then(() => { window.location.href = window.procurementRoutes.listIndex; });
            })
            .fail(xhr => Swal.fire({ icon: 'error', title: 'Save failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
    });

    $('#js-reject-requisition').on('click', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Reject this requisition?',
            input: 'text',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'e.g. Items unavailable, budget constraints...',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            inputValidator: value => !value ? 'A reason is required' : undefined,
        }).then(result => {
            if (! result.isConfirmed) return;

            $.post(window.procurementRoutes.reject, { reason: result.value })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Requisition rejected', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.procurementRoutes.listIndex; });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });
    });
});
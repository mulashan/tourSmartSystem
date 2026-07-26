$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function fmt(n) { return (Math.round(n * 100) / 100).toLocaleString('en-US'); }

    function recalc() {
        let grand = 0;
        $('#lpoItemsTable tbody tr').each(function () {
            const qty = parseFloat($(this).find('.js-qty').val()) || 0;
            const price = parseFloat($(this).find('.js-price').val()) || 0;
            const total = qty * price;
            $(this).find('.js-total').text(fmt(total));
            grand += total;
        });
        $('#js-grand-total').text(fmt(grand));
    }

    $('#lpoItemsTable').on('input', '.js-qty, .js-price', recalc);
    recalc();

    function collectPayload() {
        return {
            supplier_id: $('#supplierId').val(),
            currency_type: $('#currencyType').val(),
            requisition_description: $('#requisitionDescription').val(),
            vat_charges: $('#vatCharges').val(),
            transport_charges: $('#transportCharges').val(),
            labor_charges: $('#laborCharges').val(),
            bank_charges: $('#bankCharges').val(),
            freight_charges: $('#freightCharges').val(),
            other_charges: $('#otherCharges').val(),
            items: $('#lpoItemsTable tbody tr').map(function () {
                return {
                    lpo_item_id: $(this).data('lpo-item-id'),
                    quantity_required: parseInt($(this).find('.js-qty').val()) || 1,
                    price: parseFloat($(this).find('.js-price').val()) || 0,
                };
            }).get(),
        };
    }

    $('#js-save-draft').on('click', function () {
        $.post(window.lpoRoutes.update, collectPayload())
            .done(() => Swal.fire({ icon: 'success', title: 'Saved', timer: 1000, showConfirmButton: false }))
            .fail(xhr => Swal.fire({ icon: 'error', title: 'Save failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
    });

    $('#js-submit-approval').on('click', function () {
        $.post(window.lpoRoutes.update, collectPayload()).done(() => {
            $.post(window.lpoRoutes.submit)
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Submitted for approval', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.lpoRoutes.listIndex; });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Submit failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });
    });
});
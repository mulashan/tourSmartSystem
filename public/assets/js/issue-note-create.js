(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#issueItemsTable').on('blur', '.js-qty-issue', function () {
            const $row = $(this).closest('tr');
            const requested = parseInt($row.data('requested'));
            const balance = parseInt($row.data('balance'));
            const cap = Math.min(requested, balance);
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;
            if (qty > cap) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Maximum issuable for this item is ${cap} (limited by requested amount and store balance).` });
                qty = cap;
            }

            $(this).val(qty);
        });

        $('#js-submit-issue').on('click', function () {
            document.activeElement.blur();

            const items = $('#issueItemsTable tbody tr').map(function () {
                return {
                    requisition_item_id: $(this).data('requisition-item-id'),
                    quantity_issued: parseInt($(this).find('.js-qty-issue').val()) || 0,
                };
            }).get();

            $.post(window.issueNoteRoutes.store, { items })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Submitted for approval', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.issueNoteRoutes.newList; });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Submit failed', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });
    });
});
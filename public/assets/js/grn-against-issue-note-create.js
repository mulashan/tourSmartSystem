(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#grnItemsTable').on('blur', '.js-qty-received', function () {
            const $row = $(this).closest('tr');
            const issued = parseInt($row.data('quantity-issued'));
            let qty = parseInt($(this).val());

            if (! qty || qty < 1) qty = 1;
            if (qty > issued) {
                Swal.fire({ icon: 'warning', title: 'Quantity too high', text: `Quantity received cannot exceed quantity issued (${issued}).` });
                qty = issued;
            }

            $(this).val(qty);
        });

        $('#js-submit-grn').on('click', function () {
            document.activeElement.blur();

            if (! $('#receiptDate').val()) {
                Swal.fire({ icon: 'warning', title: 'Receipt Date required' });
                return;
            }

            const items = $('#grnItemsTable tbody tr').map(function () {
                return {
                    issue_note_item_id: $(this).data('issue-note-item-id'),
                    quantity_received: parseInt($(this).find('.js-qty-received').val()) || 0,
                };
            }).get();

            const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

            $.post(window.grnIssueRoutes.store, { receipt_date: $('#receiptDate').val(), items: items })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Submitted for approval', timer: 1200, showConfirmButton: false })
                        .then(() => { window.location.href = window.grnIssueRoutes.newList; });
                })
                .fail(xhr => {
                    $btn.prop('disabled', false).html('Submit for Approval');
                    const errors = xhr.responseJSON?.errors;
                    const firstError = errors ? Object.values(errors)[0]?.[0] : null;
                    Swal.fire({ icon: 'error', title: 'Submit failed', text: firstError || xhr.responseJSON?.message || 'Something went wrong.' });
                });
        });
    });
});
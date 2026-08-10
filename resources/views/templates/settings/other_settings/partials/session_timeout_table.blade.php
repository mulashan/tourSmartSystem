<div class="settings-panel-head"><h2>Session Timeout</h2></div>
<p class="text-muted small">Sets how many minutes of inactivity are allowed before a user working under each branch is automatically logged out.</p>

<table class="table table-hover">
    <thead><tr><th>S/N</th><th>Branch Name</th><th>Timeout (minutes)</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($branches as $i => $branch)
            <tr data-branch-id="{{ $branch->Branch_ID }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $branch->Branch_Name }}</td>
                <td><input type="number" min="5" max="480" class="form-control form-control-sm js-timeout-input" value="{{ $branch->session_timeout_minutes }}" style="max-width:120px;"></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-info text-white js-save-timeout">Save</button></td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No branches found.</td></tr>
        @endforelse
    </tbody>
</table>

<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn();
    } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });
        $('.js-save-timeout').on('click', function () {
            const $row = $(this).closest('tr');
            const branchId = $row.data('branch-id');
            const minutes = parseInt( $row.find('.js-timeout-input').val(),10);
            // Prevent submission if less than 5 minutes
            if (isNaN(minutes) || minutes < 5) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid timeout',
                    text: 'Session timeout must be at least 5 minutes.'
                });
                return; 
            }
            $.ajax({
                url: `/settings/other_settings/session-timeout/${branchId}`,
                method: 'PUT',
                data: {
                    session_timeout_minutes: minutes
                },
            })
            .done(() => Swal.fire({
                icon: 'success',
                title: 'Saved',
                timer: 1000,
                showConfirmButton: false
            }))
            .fail(xhr => Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: xhr.responseJSON?.message || 'Something went wrong.'
            }));

        });
    });
});
</script>

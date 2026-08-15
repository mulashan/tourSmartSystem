<h5 class="mb-3">Workshop Permissions</h5>

<form id="workshopPermissionsForm">
    <div class="row g-3">
        @foreach($permissions as $permission)
            <div class="col-md-6">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="permission_keys[]"
                        value="{{ $permission['key'] }}"
                        id="workshopPerm{{ $permission['key'] }}"
                        {{ in_array($permission['key'], $assignedKeys, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="workshopPerm{{ $permission['key'] }}">
                        {{ $permission['label'] }}
                        <br><small class="text-muted">{{ $permission['description'] }}</small>
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    <hr class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Permissions</button>
</form>

<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#workshopPermissionsForm').on('submit', function (e) {
            e.preventDefault();

            $.post('{{ route("users.workshop_permissions.update", $user) }}', $(this).serialize())
                .done(() => Swal.fire({ icon: 'success', title: 'Permissions saved', timer: 1200, showConfirmButton: false }))
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed to save permissions' }));
        });
    });
});
</script>

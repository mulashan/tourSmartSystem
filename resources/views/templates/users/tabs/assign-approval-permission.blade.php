<h5 class="mb-3">Approval Permissions</h5>

<form id="approvalPermissionsForm">
    <div class="row g-3">
        @foreach($permissions as $permission)
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" id="perm{{ $permission->id }}"
                        {{ in_array($permission->id, $assignedIds) ? 'checked' : '' }}>
                    <label class="form-check-label" for="perm{{ $permission->id }}">
                        {{ $permission->label }}
                        @if($permission->description)
                            <br><small class="text-muted">{{ $permission->description }}</small>
                        @endif
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    <hr class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Permissions</button>
</form>

<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('#approvalPermissionsForm').on('submit', function (e) {
        e.preventDefault();

        $.post('{{ route("users.approval_permissions.update", $user) }}', $(this).serialize())
            .done(() => Swal.fire({ icon: 'success', title: 'Permissions saved', timer: 1200, showConfirmButton: false }))
            .fail(() => Swal.fire({ icon: 'error', title: 'Failed to save permissions' }));
    });
});
</script>
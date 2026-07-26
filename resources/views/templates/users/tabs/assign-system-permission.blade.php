<h5 class="mb-2">System Permissions</h5>
<p class="text-muted small mb-4">
    Checked-and-disabled items are already granted by this user's <strong>Role</strong>. Check any additional box below to grant that specific menu to this user individually, on top of their role.
</p>

<form id="systemPermissionsForm">
    @foreach($menuGroups as $group)
        <div class="card mb-3">
            <div class="card-header bg-light fw-bold">{{ $group['title'] ?: 'Main' }}</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($group['items'] as $item)
                        @php $viaRole = in_array($item['key'], $groupGrantedKeys, true); @endphp
                        <div class="col-md-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="perm-{{ $item['key'] }}"
                                    @if($viaRole)
                                        checked disabled
                                    @else
                                        name="menu_keys[]" value="{{ $item['key'] }}"
                                        {{ in_array($item['key'], $individualGrantedKeys, true) ? 'checked' : '' }}
                                    @endif
                                >
                                <label class="form-check-label" for="perm-{{ $item['key'] }}">
                                    {{ $item['label'] }}
                                    @if($viaRole)
                                        <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size:10px;">via role</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Individual Permissions</button>
</form>

<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $('#systemPermissionsForm').on('submit', function (e) {
            e.preventDefault();

            $.post('{{ route("users.system_permissions.update", $user) }}', $(this).serialize())
                .done(() => Swal.fire({ icon: 'success', title: 'Permissions saved', timer: 1200, showConfirmButton: false }))
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed to save permissions' }));
        });
    });
});
</script>
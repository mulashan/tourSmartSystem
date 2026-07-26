<h5 class="mb-3">Assigned Sub Departments</h5>

<table class="table table-hover" id="subdepartmentTable">
    <thead><tr><th>S/N</th><th>Sub Department</th><th>Department</th><th>Branch</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($assignedSubdepartments as $i => $sub)
            <tr data-subdepartment-id="{{ $sub->Subdepartment_ID }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $sub->Subdepartment_Name }}</td>
                <td>{{ $sub->department->Department_Name ?? '—' }}</td>
                <td>{{ $sub->department->branch->Branch_Name ?? '—' }}</td>
                <td class="text-end"><button class="btn btn-sm btn-outline-danger js-remove-subdepartment">Remove</button></td>
            </tr>
        @empty
            <tr class="js-empty-row"><td colspan="5" class="text-center text-muted">No sub departments assigned.</td></tr>
        @endforelse
    </tbody>
</table>

@if($availableSubdepartments->isEmpty())
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle"></i>
        No available Sub Departments to assign — either all are already assigned, or this user has no Branches assigned yet (assign a Branch first).
    </div>
@else
    <div class="d-flex gap-2 align-items-end" style="max-width: 500px;">
        <div class="flex-grow-1">
            <label class="form-label">Add Sub Department</label>
            <select id="subdepartmentSelect" class="form-select">
                <option value="">Select...</option>
                @foreach($availableSubdepartments as $sub)
                    <option value="{{ $sub->Subdepartment_ID }}"
                        data-name="{{ $sub->Subdepartment_Name }}"
                        data-dept="{{ $sub->department->Department_Name ?? '—' }}"
                        data-branch="{{ $sub->department->branch->Branch_Name ?? '—' }}">
                        {{ $sub->Subdepartment_Name }} => {{ $sub->department->branch->Branch_Name ?? '—' }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-dark" id="js-add-subdepartment"><i class="bi bi-plus-lg"></i> Add</button>
    </div>
@endif

<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function renumber() {
            $('#subdepartmentTable tbody tr:not(.js-empty-row)').each(function (i) {
                $(this).find('td').eq(0).text(i + 1);
            });
        }

        $('#js-add-subdepartment').on('click', function () {
            const id = $('#subdepartmentSelect').val();
            if (! id) return;
            const $opt = $('#subdepartmentSelect option:selected');

            $.post('{{ route("users.subdepartments.add", $user) }}', { subdepartment_id: id }).done(() => {
                $('#subdepartmentTable .js-empty-row').remove();
                $('#subdepartmentTable tbody').append(`<tr data-subdepartment-id="${id}"><td></td><td>${$opt.data('name')}</td><td>${$opt.data('dept')}</td><td>${$opt.data('branch')}</td><td class="text-end"><button class="btn btn-sm btn-outline-danger js-remove-subdepartment">Remove</button></td></tr>`);
                renumber();
                $opt.remove();
            }).fail(xhr => Swal.fire({ icon: 'error', title: 'Failed to add', text: xhr.responseJSON?.message || 'Something went wrong.' }));
        });

        $('#subdepartmentTable').on('click', '.js-remove-subdepartment', function () {
            const $row = $(this).closest('tr');
            const id = $row.data('subdepartment-id');

            Swal.fire({ icon: 'warning', title: 'Remove this sub department?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
                if (! result.isConfirmed) return;

                $.ajax({ url: `/users/{{ $user->id }}/subdepartments/${id}`, method: 'DELETE' }).done(() => {
                    $row.remove();
                    renumber();
                    if (! $('#subdepartmentTable tbody tr').length) {
                        $('#subdepartmentTable tbody').append('<tr class="js-empty-row"><td colspan="5" class="text-center text-muted">No sub departments assigned.</td></tr>');
                    }
                }).fail(() => Swal.fire({ icon: 'error', title: 'Failed to remove sub department' }));
            });
        });
    });
});
</script>
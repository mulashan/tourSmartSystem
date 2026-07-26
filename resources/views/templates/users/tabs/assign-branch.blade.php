<h5 class="mb-3">Assigned Branches</h5>

<table class="table table-hover" id="branchTable">
    <thead><tr><th>S/N</th><th>Branch Name</th><th class="text-end">Action</th></tr></thead>
    <tbody>
        @forelse($assignedBranches as $i => $branch)
            <tr data-branch-id="{{ $branch->Branch_ID }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $branch->Branch_Name }}</td>
                <td class="text-end"><button class="btn btn-sm btn-outline-danger js-remove-branch">Remove</button></td>
            </tr>
        @empty
            <tr class="js-empty-row"><td colspan="3" class="text-center text-muted">No branches assigned.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex gap-2 align-items-end" style="max-width: 500px;">
    <div class="flex-grow-1">
        <label class="form-label">Add Branch</label>
        <select id="branchSelect" class="form-select">
            <option value="">Select...</option>
            @foreach($availableBranches as $branch)
                <option value="{{ $branch->Branch_ID }}" data-name="{{ $branch->Branch_Name }}">{{ $branch->Branch_Name }}</option>
            @endforeach
        </select>
    </div>
    <button type="button" class="btn btn-dark" id="js-add-branch"><i class="bi bi-plus-lg"></i> Add</button>
</div>

<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    function renumber() {
        $('#branchTable tbody tr:not(.js-empty-row)').each(function (i) {
            $(this).find('td').eq(0).text(i + 1);
        });
    }

    $('#js-add-branch').on('click', function () {
        const id = $('#branchSelect').val();
        if (! id) return;
        const name = $('#branchSelect option:selected').data('name');

        $.post('{{ route("users.branches.add", $user) }}', { branch_id: id }).done(() => {
            $('#branchTable .js-empty-row').remove();
            $('#branchTable tbody').append(`<tr data-branch-id="${id}"><td></td><td>${name}</td><td class="text-end"><button class="btn btn-sm btn-outline-danger js-remove-branch">Remove</button></td></tr>`);
            renumber();
            $('#branchSelect option:selected').remove();
        }).fail(() => Swal.fire({ icon: 'error', title: 'Failed to add branch' }));
    });

    $('#branchTable').on('click', '.js-remove-branch', function () {
        const $row = $(this).closest('tr');
        const id = $row.data('branch-id');
        const name = $row.find('td').eq(1).text();

        Swal.fire({ icon: 'warning', title: 'Remove this branch?', showCancelButton: true, confirmButtonText: 'Yes, remove it' }).then(result => {
            if (! result.isConfirmed) return;

            $.ajax({ url: `/users/{{ $user->id }}/branches/${id}`, method: 'DELETE' }).done(() => {
                $row.remove();
                renumber();
                $('#branchSelect').append(`<option value="${id}" data-name="${name}">${name}</option>`);
                if (! $('#branchTable tbody tr').length) {
                    $('#branchTable tbody').append('<tr class="js-empty-row"><td colspan="3" class="text-center text-muted">No branches assigned.</td></tr>');
                }
            }).fail(() => Swal.fire({ icon: 'error', title: 'Failed to remove branch' }));
        });
    });
});
</script>
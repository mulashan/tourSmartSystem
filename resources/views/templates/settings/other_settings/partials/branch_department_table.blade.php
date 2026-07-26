<div class="settings-panel-head">
    <h2>Branch Departments</h2>
    <button type="button" class="btn btn-info text-white js-add-department">
        <i class="bi bi-plus-lg"></i> New Department
    </button>
</div>

<input type="text" class="form-control mb-3 js-department-search" placeholder="Search Departments">

<table class="table table-hover">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Department Name</th>
            <th>Branch</th>
            <th>Department Nature</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->Department_Name }}</td>
                <td>{{ $item->branch->Branch_Name ?? '—' }}</td>
                <td>{{ $item->departmentNature->department_nature ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary js-edit-department"
                        data-id="{{ $item->Department_ID }}"
                        data-name="{{ $item->Department_Name }}"
                        data-branch-id="{{ $item->Branch_ID }}"
                        data-nature-id="{{ $item->department_nature_id }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!--button class="btn btn-sm btn-outline-danger js-delete-department" data-id="{{ $item->Department_ID }}">
                        <i class="bi bi-trash"></i>
                    </button-->
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No departments yet.</td></tr>
        @endforelse
    </tbody>
</table>
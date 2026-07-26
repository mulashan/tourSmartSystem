<div class="settings-panel-head">
    <h2>Subdepartments</h2>
    <button type="button" class="btn btn-info text-white js-add-subdepartment">
        <i class="bi bi-plus-lg"></i> New Subdepartment
    </button>
</div>

<input type="text" class="form-control mb-3 js-subdepartment-search" placeholder="Search Subdepartments">

<table class="table table-hover">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Subdepartment Name</th>
            <th>Department</th>
            <th>Nature</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->Subdepartment_Name }}</td>
                <td>{{ $item->department->Department_Name ?? '—' }}</td>
                <td>{{ $item->department->departmentNature->department_nature ?? '—' }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary js-edit-subdepartment"
                        data-id="{{ $item->Subdepartment_ID }}"
                        data-name="{{ $item->Subdepartment_Name }}"
                        data-department-id="{{ $item->Department_ID }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!--button class="btn btn-sm btn-outline-danger js-delete-subdepartment" data-id="{{ $item->Subdepartment_ID }}">
                        <i class="bi bi-trash"></i>
                    </button-->
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No subdepartments yet.</td></tr>
        @endforelse
    </tbody>
</table>
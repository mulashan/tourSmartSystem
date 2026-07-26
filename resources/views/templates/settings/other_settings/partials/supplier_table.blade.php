<div class="settings-panel-head">
    <h2>Suppliers</h2>
    <button type="button" class="btn btn-info text-white js-add-supplier">
        <i class="bi bi-plus-lg"></i> New Supplier
    </button>
</div>

<input type="text" class="form-control mb-3 js-supplier-search" placeholder="Search Suppliers">

<table class="table table-hover">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Supplier Name</th>
            <th>Contact Person</th>
            <th>Mobile</th>
            <th>Email</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->supplier_name }}</td>
                <td>{{ $item->contact_person_name }}</td>
                <td>{{ $item->contact_person_mobile }}</td>
                <td>{{ $item->contact_person_email }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary js-edit-supplier"
                        data-id="{{ $item->id }}"
                        data-supplier-name="{{ $item->supplier_name }}"
                        data-supplier-address="{{ $item->supplier_address }}"
                        data-postal-address="{{ $item->postal_address }}"
                        data-contact-person-name="{{ $item->contact_person_name }}"
                        data-contact-person-mobile="{{ $item->contact_person_mobile }}"
                        data-contact-person-email="{{ $item->contact_person_email }}"
                        data-telephone="{{ $item->telephone }}"
                        data-fax="{{ $item->fax }}"
                        data-physical-address="{{ $item->physical_address }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!--button class="btn btn-sm btn-outline-danger js-delete-supplier" data-id="{{ $item->id }}">
                        <i class="bi bi-trash"></i>
                    </button-->
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No suppliers yet.</td></tr>
        @endforelse
    </tbody>
</table>
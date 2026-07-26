<div class="settings-panel-head">
    <h2>{{ $config['label'] }}</h2>
    <button type="button" class="btn btn-info text-white js-add-lookup" data-key="{{ $key }}">
        <i class="bi bi-plus-lg"></i> New {{ $config['singular'] }}
    </button>
</div>

<input type="text" class="form-control mb-3 js-lookup-search" data-key="{{ $key }}" placeholder="Search {{ $config['label'] }}">

<table class="table table-hover">
    <thead>
        <tr>
            <th>S/N</th>
            <th>Name</th>
            <th>Code</th>
            <th>Description</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary js-edit-lookup"
                        data-key="{{ $key }}" data-id="{{ $item->id }}"
                        data-name="{{ $item->name }}" data-code="{{ $item->code }}"
                        data-description="{{ $item->description }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!--button class="btn btn-sm btn-outline-danger js-delete-lookup" data-key="{{ $key }}" data-id="{{ $item->id }}">
                        <i class="bi bi-trash"></i>
                    </button-->
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No entries yet.</td></tr>
        @endforelse
    </tbody>
</table>
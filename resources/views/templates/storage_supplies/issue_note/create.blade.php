@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New Issue Note — Requisition #{{ $requisition->id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Store Requesting</label><input type="text" class="form-control" value="{{ $requisition->requestingSubdepartment->Subdepartment_Name ?? '—' }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Officer</label><input type="text" class="form-control" value="{{ $requisition->officer->name ?? '—' }}" disabled></div>
            <div class="col-md-4"><label class="form-label">Description</label><input type="text" class="form-control" value="{{ $requisition->description }}" disabled></div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover" id="issueItemsTable" style="min-width:900px;">
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Requested</th><th>Store Balance</th><th>Quantity to Issue</th></tr></thead>
        <tbody>
            @foreach($requisition->items as $i => $line)
                @php $balance = $balances->get($line->item_id, 0); @endphp
                <tr data-requisition-item-id="{{ $line->id }}" data-requested="{{ $line->quantity_requested }}" data-balance="{{ $balance }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $line->quantity_requested }}</td>
                    <td>{{ $balance }}</td>
                    <td><input type="number" min="1" max="{{ min($line->quantity_requested, $balance) }}" class="form-control form-control-sm js-qty-issue" value="{{ min($line->quantity_requested, $balance) }}"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.issue_note.new') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-submit-issue">Submit for Approval</button>
</div>
@endsection

@section('scripts')
<script>
    window.issueNoteRoutes = {
        store: '{{ route("storage_supplies.issue_note.store", $requisition->id) }}',
        newList: '{{ route("storage_supplies.issue_note.new") }}',
    };
</script>
<script src="{{ asset('assets/js/issue-note-create.js') }}"></script>
@endsection
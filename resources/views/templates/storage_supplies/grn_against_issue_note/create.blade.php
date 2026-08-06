@extends('templates.app')

@section('content')
@include('templates.storage_supplies.partials.active_subdepartment_bar')

<div class="card mb-3">
    <div class="card-header">New GRN — Against Issue Note #{{ $issueNote->id }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Store Issuing</label><input type="text" class="form-control" value="{{ $issueNote->requisition->issuingSubdepartment->Subdepartment_Name ?? '—' }}" disabled></div>
            <div class="col-md-6"><label class="form-label">Receipt Date *</label><input type="date" id="receiptDate" class="form-control" value="{{ now()->toDateString() }}" required></div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover" id="grnItemsTable" style="min-width:800px;">
        <thead><tr><th>S/N</th><th>Item Name</th><th>UoM</th><th>Quantity Issued</th><th>Quantity Received</th></tr></thead>
        <tbody>
            @foreach($issueNote->items as $i => $line)
                <tr data-issue-note-item-id="{{ $line->id }}" data-quantity-issued="{{ $line->quantity_issued }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->product_name ?? '—' }}</td>
                    <td>{{ $line->item->unitOfMeasure->name ?? '-' }}</td>
                    <td>{{ $line->quantity_issued }}</td>
                    <td><input type="number" min="1" max="{{ $line->quantity_issued }}" class="form-control form-control-sm js-qty-received" value="{{ $line->quantity_issued }}"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="text-end mt-3">
    <a href="{{ route('storage_supplies.grn_against_issue_note.new') }}" class="btn btn-secondary">Cancel</a>
    <button type="button" class="btn btn-info text-white" id="js-submit-grn">Submit for Approval</button>
</div>
@endsection

@section('scripts')
<script>
    window.grnIssueRoutes = {
        store: '{{ route("storage_supplies.grn_against_issue_note.store", $issueNote->id) }}',
        newList: '{{ route("storage_supplies.grn_against_issue_note.new") }}',
    };
</script>
<script src="{{ asset('assets/js/grn-against-issue-note-create.js') }}"></script>
@endsection
@extends('templates.app')

@section('content')
<div class="settings-panel-head">
    <h2>Assign Sub Departments — {{ $targetUser->name }}</h2>
</div>

<form id="assignSubdepartmentsForm">
    @foreach($subdepartments as $sub)
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="subdepartment_ids[]" value="{{ $sub->Subdepartment_ID }}" id="assign{{ $sub->Subdepartment_ID }}"
                {{ in_array($sub->Subdepartment_ID, $assignedIds) ? 'checked' : '' }}>
            <label class="form-check-label" for="assign{{ $sub->Subdepartment_ID }}">{{ $sub->Subdepartment_Name }}</label>
        </div>
    @endforeach
    <button type="submit" class="btn btn-primary mt-3">Save</button>
    <div class="text-success small mt-2 d-none" id="assignSuccess">Saved.</div>
</form>

<script>
$('#assignSubdepartmentsForm').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
        url: '{{ route("users.subdepartments.update", $targetUser->id) }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        data: $(this).serialize(),
    }).done(() => $('#assignSuccess').removeClass('d-none'));
});
</script>
@endsection
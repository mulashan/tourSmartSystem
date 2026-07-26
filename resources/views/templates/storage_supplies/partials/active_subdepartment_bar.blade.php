@php
    $activeSub = \App\Models\Subdepartment::find(session('active_subdepartment_id'));
    $activeModule = session('active_subdepartment_module');
@endphp
<div class="settings-panel-head border rounded p-2 mb-3 bg-light">
    <div><strong>Sub Department</strong> &nbsp; {{ $activeSub->Subdepartment_Name ?? '—' }}</div>
    <a href="{{ route('storage-supplies.select-subdepartment', ['module' => $activeModule]) }}" class="btn btn-dark btn-sm">Change</a>
</div>
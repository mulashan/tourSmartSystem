@extends('templates.app')

@php
    $employmentTypes = collect($employmentTypes ?? []);
    $branches = collect($branches ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>HR Employment Types</h1><p>Manage employment type records by branch for employee setup.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addEmploymentTypeModal"><i class="bi bi-plus"></i> Add Employment Type</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $employmentTypes->count() }}</span></button><button>Branches <span>{{ $branches->count() }}</span></button><button>Active <span>{{ $employmentTypes->count() }}</span></button><button>Inactive <span>0</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search employment type..."><button><i class="bi bi-sliders"></i> Branch</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Employment Type</th><th>Branch</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($employmentTypes as $type)
                        @php $branch = $branches->firstWhere('Branch_ID', $type->branch_id); @endphp
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($type->name, 0, 1) }}</span><strong>{{ $type->name }}</strong><small>Type #{{ $type->id }}</small></td>
                            <td>{{ $branch->Branch_Name ?? 'All branches' }}</td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td>{{ optional($type->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($employmentTypes->isEmpty())
                        <tr><td colspan="6">No HR employment types added yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Employment Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $employmentTypes->count() }}</strong><small>Saved types</small></div><div class="green-bg"><span>Branches</span><strong>{{ $branches->count() }}</strong><small>Available</small></div></div></section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($employmentTypes->take(3) as $type)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($type->name, 0, 1) }}</span><span><strong>{{ $type->name }}</strong><small>{{ optional($type->created_at)->format('M j, Y') }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addEmploymentTypeModal" tabindex="-1" aria-labelledby="addEmploymentTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="addEmploymentTypeModalLabel">Add Employment Type</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="{{ route('settings.hr-employment-types.store') }}" method="post">
                @csrf
                <div class="modal-body"><div class="add-user-grid">
                    <label class="wide">Name<input type="text" name="name" value="{{ old('name') }}" placeholder="Enter employment type" required></label>
                    <label class="wide">Branch<select name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->Branch_ID }}" @selected((string) old('branch_id') === (string) $branch->Branch_ID)>{{ $branch->Branch_Name }}</option>@endforeach</select></label>
                </div></div>
                <div class="modal-footer"><button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="modal-submit">Add Employment Type</button></div>
            </form>
        </div></div>
    </div>
@endsection

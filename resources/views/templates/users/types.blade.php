@extends('templates.app')

@php
    $userTypes = collect($userTypes ?? []);
    $totalTypes = $userTypes->count();
    $activeTypes = $userTypes->where('priv_status', true)->count();
    $inactiveTypes = $userTypes->where('priv_status', false)->count();
    $highestAccess = $userTypes->max('access_level_id') ?? 0;
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>User Types Directory</h1><p>Manage user privilege types, descriptions, access levels, and active status.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addUserTypeModal"><i class="bi bi-plus"></i> Add User Types</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $totalTypes }}</span></button><button>Active <span>{{ $activeTypes }}</span></button><button>Inactive <span>{{ $inactiveTypes }}</span></button><button>Max Level <span>{{ $highestAccess }}</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search user type, description..."><button><i class="bi bi-sliders"></i> Status</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>User Type</th><th>Description</th><th>Access Level</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($userTypes as $type)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($type->privilege_name, 0, 1) }}</span><strong>{{ $type->privilege_name }}</strong><small>#TYPE-{{ str_pad((string) $type->id, 3, '0', STR_PAD_LEFT) }}</small></td>
                            <td>{{ $type->privilege_desc }}</td>
                            <td><span class="role-pill manager"><i class="bi bi-shield-check"></i> Level {{ $type->access_level_id }}</span></td>
                            <td><span class="state-dot {{ $type->priv_status ? 'active' : 'inactive' }}"></span>{{ $type->priv_status ? 'Active' : 'Inactive' }}</td>
                            <td>{{ optional($type->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($userTypes->isEmpty())
                        <tr>
                            <td colspan="7">No user types added yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="table-footer"><span>Showing {{ $totalTypes }} of {{ $totalTypes }} user types</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button><i class="bi bi-chevron-right"></i></button></div></div>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>User Types Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $totalTypes }}</strong><small>Saved types</small></div><div class="green-bg"><span>Active</span><strong>{{ $activeTypes }}</strong><small>Can be assigned</small></div><div class="amber-bg"><span>Max Level</span><strong>{{ $highestAccess }}</strong><small>Highest access</small></div><div class="red-bg"><span>Inactive</span><strong>{{ $inactiveTypes }}</strong><small>Hidden from use</small></div></div></section>
            <section class="panel"><h2>Access Distribution</h2>@foreach($userTypes->take(3) as $type)<div class="target-row {{ $loop->iteration === 1 ? 'redbar' : ($loop->iteration === 2 ? 'amber' : 'violet') }}"><span>{{ $type->privilege_name }}</span><strong>{{ $type->access_level_id }}</strong><div><i style="width:{{ min($type->access_level_id * 10, 100) }}%"></i></div></div>@endforeach</section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($userTypes->take(3) as $type)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($type->privilege_name,0,1) }}</span><span><strong>{{ $type->privilege_name }}</strong><small>{{ optional($type->created_at)->format('M j, Y') }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addUserTypeModal" tabindex="-1" aria-labelledby="addUserTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addUserTypeModalLabel">Add User Type</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.types.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="add-user-grid">
                            <label>User Type Name
                                <input type="text" name="privilege_name" value="{{ old('privilege_name') }}" placeholder="Enter user type name" required>
                            </label>
                            <label>Access Level
                                <input type="number" name="access_level_id" value="{{ old('access_level_id', 1) }}" min="1" placeholder="Enter access level" required>
                            </label>
                            <label class="wide">Description
                                <input type="text" name="privilege_desc" value="{{ old('privilege_desc') }}" placeholder="Enter user type description" required>
                            </label>
                            <label class="wide">Status
                                <select name="priv_status" required>
                                    <option value="1" @selected(old('priv_status', '1') === '1')>Active</option>
                                    <option value="0" @selected(old('priv_status') === '0')>Inactive</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-submit">Add User Type</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

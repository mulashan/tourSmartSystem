@extends('templates.app')

@php
    $users = collect($users ?? []);
    $branches = collect($branches ?? []);
    $userTypes = collect($userTypes ?? []);
    $totalUsers = $users->count();
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>People Directory</h1><p>Manage member access, lifecycle status, and team distribution from one control surface.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-plus"></i> Add User</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $totalUsers }}</span></button><button>Active <span>{{ $totalUsers }}</span></button><button>Pending <span>0</span></button><button>Inactive <span>0</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search users, email, role..."><button><i class="bi bi-sliders"></i> Role</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>User</th><th>Role</th><th>Status</th><th>Branch</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($users as $user)
                        @php
                            $fullName = trim($user->name ?? '');
                            $type = $userTypes->firstWhere('id', $user->privilege_id);
                            $branch = $branches->firstWhere('Branch_ID', $user->branch_id);
                        @endphp
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($fullName ?: $user->email, 0, 1) }}</span><strong>{{ $fullName ?: $user->email }}</strong><small>{{ $user->email }}</small></td>
                            <td><span class="role-pill user"><i class="bi bi-person"></i> {{ $type->privilege_name ?? 'Not assigned' }}</span></td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td>{{ $branch->Branch_Name ?? 'Not assigned' }}</td>
                            <td>{{ optional($user->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($users->isEmpty())
                        <tr>
                            <td colspan="7">No users added yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="table-footer"><span>Showing {{ $totalUsers }} of {{ $totalUsers }} users</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button><i class="bi bi-chevron-right"></i></button></div></div>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Directory Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $totalUsers }}</strong><small>Saved users</small></div><div class="green-bg"><span>Active</span><strong>{{ $totalUsers }}</strong><small>Can login</small></div><div class="amber-bg"><span>User Types</span><strong>{{ $userTypes->count() }}</strong><small>Available roles</small></div><div class="red-bg"><span>Branches</span><strong>{{ $branches->count() }}</strong><small>Available branches</small></div></div></section>
            <section class="panel"><h2>Role Distribution</h2>@foreach($userTypes->take(3) as $type)<div class="target-row {{ $loop->iteration === 1 ? 'redbar' : ($loop->iteration === 2 ? 'amber' : 'violet') }}"><span>{{ $type->privilege_name }}</span><strong>{{ $users->where('privilege_id', $type->id)->count() }}</strong><div><i style="width:{{ $totalUsers ? min(($users->where('privilege_id', $type->id)->count() / $totalUsers) * 100, 100) : 0 }}%"></i></div></div>@endforeach</section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($users->take(3) as $user)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($user->name ?: $user->email,0,1) }}</span><span><strong>{{ $user->name ?: $user->email }}</strong><small>{{ optional($user->created_at)->format('M j, Y') }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addUserModalLabel">Add New User</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="add-user-grid">
                            <label class="wide">Full Name
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
                            </label>
                            <label class="wide">Role
                                <select name="privilege_id" required>
                                    <option selected disabled value="">Select role...</option>
                                    @foreach($userTypes as $type)
                                        <option value="{{ $type->id }}" @selected((string) old('privilege_id') === (string) $type->id)>{{ $type->privilege_name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="wide">Branch
                                <select name="branch_id" required>
                                    <option selected disabled value="">Select branch...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Branch_ID }}" @selected((string) old('branch_id') === (string) $branch->Branch_ID)>{{ $branch->Branch_Name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="wide">Username
                                <input type="text" name="email" value="{{ old('email') }}" placeholder="Enter email or phone number" required>
                            </label>
                            <label>Password
                                <input type="password" name="password" placeholder="Enter password" required>
                            </label>
                            <label>Confirm Password
                                <input type="password" name="password_confirmation" placeholder="Confirm password" required>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-submit">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

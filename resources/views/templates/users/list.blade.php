@extends('templates.app')

@section('content')

<div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Users</h1>
        <p class="text-muted mb-0">
            Manage system users, roles and branch assignments.
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#addUserModal">
        <i class="bi bi-plus-circle"></i> New User
    </button>
</div>

<section class="section">

    <div class="card">
        <div class="card-body">

            <!-- Filters -->
            <div class="row mt-3 mb-4">

                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Search users...">
                    </div>
                </div>

                <div class="col-lg-2">
                    <select class="form-select">
                        <option>All Roles</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <select class="form-select">
                        <option>All Branches</option>
                    </select>
                </div>

                <div class="col-lg-4 text-end">
                    <button class="btn btn-outline-success">
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>

            </div>

            <!-- Table -->
            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox">
                            </th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox">
                                </td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ optional($userTypes->firstWhere('id', $user->privilege_id))->privilege_name }}
                                    </span>
                                </td>
                                <td>
                                    {{ optional($branches->firstWhere('Branch_ID', $user->branch_id))->Branch_Name }}
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('users.show', $user) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Manage User">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="text-muted">
                    Showing {{ $users->count() }} users
                </span>
            </div>
        </div>
    </div>
</section>
<!-- Add User Modal -->
<div
    class="modal fade"
    id="addUserModal"
    tabindex="-1"
    aria-labelledby="addUserModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">
                        Add New User
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Full Name
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="name"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Username / Email
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                name="email"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Role
                            </label>
                            <select
                                class="form-select"
                                name="privilege_id"
                                required>
                                <option value="">Select Role</option>
                                @foreach($userTypes as $type)
                                    <option value="{{ $type->id }}">
                                        {{ $type->privilege_name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Branch
                            </label>

                            <select
                                class="form-select"
                                name="branch_id"
                                required>

                                <option value="">Select Branch</option>

                                @foreach($branches as $branch)
                                    <option value="{{ $branch->Branch_ID }}">
                                        {{ $branch->Branch_Name }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="password"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="password_confirmation"
                                required>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>
                        Save User
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
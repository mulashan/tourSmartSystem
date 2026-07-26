@extends('templates.app')

@section('title', 'Edit User')

@section('content')

<div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Edit User</h1>
        <p class="text-muted mb-0">
            Update user information, role and branch assignment.
        </p>
    </div>

    <a href="{{ route('users.list') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
        Back to Users
    </a>
</div>

<section class="section">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">

            <strong>Please correct the following errors:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    <div class="card">

        <div class="card-header">

            <h5 class="card-title mb-0">
                User Details
            </h5>

        </div>

        <div class="card-body">

            <form
                action="{{ route('users.update', $user) }}"
                method="POST">

                @csrf
                @method('PUT')

                <div class="row g-4 mt-2">

                    <!-- Full Name -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}"
                            required>

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- Username -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Username / Email
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            required>

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- Role -->
                    <div class="col-md-6">

                        <label class="form-label">
                            User Role
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="privilege_id"
                            class="form-select @error('privilege_id') is-invalid @enderror"
                            required>

                            <option value="">
                                Select Role
                            </option>

                            @foreach($userTypes as $type)

                                <option
                                    value="{{ $type->id }}"
                                    @selected(old('privilege_id', $user->privilege_id) == $type->id)>

                                    {{ $type->privilege_name }}

                                </option>

                            @endforeach

                        </select>

                        @error('privilege_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- Branch -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Branch
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="branch_id"
                            class="form-select @error('branch_id') is-invalid @enderror"
                            required>

                            <option value="">
                                Select Branch
                            </option>

                            @foreach($branches as $branch)

                                <option
                                    value="{{ $branch->Branch_ID }}"
                                    @selected(old('branch_id', $user->branch_id) == $branch->Branch_ID)>

                                    {{ $branch->Branch_Name }}

                                </option>

                            @endforeach

                        </select>

                        @error('branch_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <hr class="my-2">

                    <div class="col-12">

                        <div class="alert alert-info mb-0">

                            <i class="bi bi-info-circle"></i>

                            Leave the password fields empty if you don't want to change the user's password.

                        </div>

                    </div>

                    <!-- Password -->
                    <div class="col-md-6">

                        <label class="form-label">
                            New Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror">

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">

                        <label class="form-label">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control">

                    </div>

                </div>

                <hr class="mt-5">

                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="{{ route('users.list') }}"
                        class="btn btn-secondary">

                        <i class="bi bi-x-circle"></i>

                        Cancel

                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-check-circle"></i>

                        Update User

                    </button>

                </div>

            </form>

        </div>

    </div>

</section>

@endsection
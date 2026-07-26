<form action="{{ route('users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Username / Email <span class="text-danger">*</span></label>
            <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">User Role <span class="text-danger">*</span></label>
            <select name="privilege_id" class="form-select @error('privilege_id') is-invalid @enderror" required>
                <option value="">Select Role</option>
                @foreach($userTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('privilege_id', $user->privilege_id) == $type->id)>{{ $type->privilege_name }}</option>
                @endforeach
            </select>
            @error('privilege_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12"><hr class="my-2"></div>
        <div class="col-12">
            <div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Leave the password fields empty if you don't want to change the user's password.</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
    </div>

    <hr class="mt-5">
    <div class="d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update User</button>
    </div>
</form>
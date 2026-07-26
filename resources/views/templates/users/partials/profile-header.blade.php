<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row align-items-center gy-4">

            <!-- Avatar -->
            <div class="col-md-auto text-center">
                @if($user->photo)
                    <img src="{{ asset($user->photo) }}" class="rounded-circle" style="width:96px;height:96px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                         style="width:96px;height:96px;font-size:36px;font-weight:600;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Identity + meta -->
            <div class="col-md">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <div class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="bi bi-circle-fill" style="font-size:8px; vertical-align:middle;"></i> Active
                    </span>
                </div>

                <div class="row gy-3">
                    <div class="col-6 col-md-3">
                        <div class="text-uppercase text-muted small mb-1" style="font-size:11px; letter-spacing:.03em;">Role</div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ optional($userTypes->firstWhere('id', $user->privilege_id))->privilege_name ?? '—' }}
                        </span>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-uppercase text-muted small mb-1" style="font-size:11px; letter-spacing:.03em;">Branches</div>
                        @forelse($branches as $branch)
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $branch->Branch_Name }}</span>
                        @empty
                            <span class="text-muted">None assigned</span>
                        @endforelse
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-uppercase text-muted small mb-1" style="font-size:11px; letter-spacing:.03em;">Created</div>
                        <div class="fw-semibold">{{ optional($user->created_at)->format('d M Y') ?? '—' }}</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="text-uppercase text-muted small mb-1" style="font-size:11px; letter-spacing:.03em;">Last Login</div>
                        <div class="fw-semibold">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@extends('templates.app')

@php
    $branches = collect($branches ?? []);
    $totalBranches = $branches->count();
    $activeBranches = $branches->where('status', 'Active')->count();
    $pendingBranches = $branches->where('status', 'Pending')->count();
    $inactiveBranches = $branches->where('status', 'Inactive')->count();
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="page-hero">
        <div><h1>Branch Directory</h1><p>Manage company branches, assigned managers, locations, and operating status from one control surface.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addBranchModal"><i class="bi bi-plus"></i> Add Branch</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $totalBranches }}</span></button><button>Active <span>{{ $activeBranches }}</span></button><button>Pending <span>{{ $pendingBranches }}</span></button><button>Inactive <span>{{ $inactiveBranches }}</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search branch, code, location..."><button><i class="bi bi-sliders"></i> Status</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Branch</th><th>Manager</th><th>Status</th><th>Location</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($branches as $branch)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $branch['tone'] }}">{{ substr($branch['name'], 0, 1) }}</span><strong>{{ $branch['name'] }}</strong><small>{{ $branch['code'] }}</small></td>
                            <td><span class="role-pill manager"><i class="bi bi-person"></i> {{ $branch['manager'] }}</span></td>
                            <td><span class="state-dot {{ strtolower($branch['status']) }}"></span>{{ $branch['status'] }}</td>
                            <td>{{ $branch['location'] }}</td>
                            <td>{{ $branch['created'] }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($branches->isEmpty())
                        <tr>
                            <td colspan="7">No branches added yet.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="table-footer"><span>Showing {{ $totalBranches }} of {{ $totalBranches }} branches</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button><i class="bi bi-chevron-right"></i></button></div></div>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Branch Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $totalBranches }}</strong><small>Saved branches</small></div><div class="green-bg"><span>Active</span><strong>{{ $activeBranches }}</strong><small>Fully operating</small></div><div class="amber-bg"><span>Pending</span><strong>{{ $pendingBranches }}</strong><small>Setup required</small></div><div class="red-bg"><span>Inactive</span><strong>{{ $inactiveBranches }}</strong><small>Needs review</small></div></div></section>
            <section class="panel"><h2>Region Distribution</h2><div class="target-row redbar"><span>Northern Zone</span><strong>8</strong><div><i style="width:34%"></i></div></div><div class="target-row amber"><span>Coastal Zone</span><strong>10</strong><div><i style="width:56%"></i></div></div><div class="target-row violet"><span>Lake Zone</span><strong>6</strong><div><i style="width:78%"></i></div></div></section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($branches->take(3) as $branch)<div class="mini-person"><span class="tiny-avatar {{ $branch['tone'] }}">{{ substr($branch['name'],0,1) }}</span><span><strong>{{ $branch['name'] }}</strong><small>{{ $branch['created'] }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="addBranchModalLabel">Add New Branch</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('settings.branch.store') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="add-user-grid">
                            <label>Branch Name
                                <input type="text" name="branch_name" value="{{ old('branch_name') }}" placeholder="Enter branch name" required>
                            </label>
                            <label class="wide">Location
                                <input type="text" name="location" value="{{ old('location') }}" placeholder="Enter branch location">
                            </label>
                            <label class="wide">Manager
                                <select name="manager">
                                    <option selected disabled>Select manager...</option>
                                    <option @selected(old('manager') === 'Sarah Johnson')>Sarah Johnson</option>
                                    <option @selected(old('manager') === 'Michael Chen')>Michael Chen</option>
                                    <option @selected(old('manager') === 'Emily Rodriguez')>Emily Rodriguez</option>
                                </select>
                            </label>
                            <label>Status
                                <select name="status">
                                    <option @selected(old('status') === 'Active')>Active</option>
                                    <option @selected(old('status') === 'Pending')>Pending</option>
                                    <option @selected(old('status') === 'Inactive')>Inactive</option>
                                </select>
                            </label>
                           
                            <label>Token
                                <input type="text" name="token" value="{{ old('token') }}" placeholder="Enter branch token">
                            </label>
                            <label>Token Date
                                <input type="date" name="token_date" value="{{ old('token_date') }}">
                            </label>
                            <label class="wide">Banner Link
                                <input type="url" name="banner_link" value="{{ old('banner_link') }}" placeholder="https://example.com/banner.jpg">
                            </label>
                            <label class="modal-check wide">
                                <input type="checkbox" name="activate_branch" checked>
                                <span>Activate branch after saving</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-submit">Add Branch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

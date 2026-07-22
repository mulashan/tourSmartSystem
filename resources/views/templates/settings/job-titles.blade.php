@extends('templates.app')

@php
    $jobTitles = collect($jobTitles ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Job Titles</h1><p>Manage employee titles and title locations for HR setup.</p></div>
        <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addJobTitleModal"><i class="bi bi-plus"></i> Add Job Title</button></div>
    </section>

    <div class="content-split">
        <section class="panel table-panel">
            <div class="tabs-toolbar">
                <div class="soft-tabs"><button class="active">All <span>{{ $jobTitles->count() }}</span></button><button>Others <span>{{ $jobTitles->where('title_location', 'others')->count() }}</span></button><button>Active <span>{{ $jobTitles->count() }}</span></button><button>Inactive <span>0</span></button></div>
                <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search title, location..."><button><i class="bi bi-sliders"></i> Location</button></div>
            </div>
            <table class="nice-table directory-table">
                <thead><tr><th><input type="checkbox"></th><th>Title</th><th>Location</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($jobTitles as $title)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><span class="tiny-avatar {{ $loop->iteration % 2 ? 'teal' : 'indigo' }}">{{ substr($title->title ?: 'T', 0, 1) }}</span><strong>{{ $title->title }}</strong><small>Title #{{ $title->id }}</small></td>
                            <td>{{ $title->title_location }}</td>
                            <td><span class="state-dot active"></span>Active</td>
                            <td>{{ optional($title->created_at)->format('M j, Y') }}</td>
                            <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                        </tr>
                    @endforeach
                    @if($jobTitles->isEmpty())
                        <tr><td colspan="6">No job titles added yet.</td></tr>
                    @endif
                </tbody>
            </table>
        </section>

        <aside class="side-stack">
            <section class="panel"><h2>Title Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>{{ $jobTitles->count() }}</strong><small>Saved titles</small></div><div class="green-bg"><span>Active</span><strong>{{ $jobTitles->count() }}</strong><small>Ready to use</small></div></div></section>
            <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach($jobTitles->take(3) as $title)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($title->title ?: 'T', 0, 1) }}</span><span><strong>{{ $title->title }}</strong><small>{{ $title->title_location }}</small></span></div>@endforeach</section>
        </aside>
    </div>

    <div class="modal fade add-user-modal" id="addJobTitleModal" tabindex="-1" aria-labelledby="addJobTitleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title" id="addJobTitleModalLabel">Add Job Title</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form action="{{ route('settings.job-titles.store') }}" method="post">
                @csrf
                <div class="modal-body"><div class="add-user-grid">
                    <label class="wide">Title<input type="text" name="title" value="{{ old('title') }}" placeholder="Enter job title" required></label>
                    <label class="wide">Title Location<input type="text" name="title_location" value="{{ old('title_location', 'others') }}" placeholder="Enter title location" required></label>
                </div></div>
                <div class="modal-footer"><button type="button" class="modal-cancel" data-bs-dismiss="modal">Cancel</button><button type="submit" class="modal-submit">Add Job Title</button></div>
            </form>
        </div></div>
    </div>
@endsection

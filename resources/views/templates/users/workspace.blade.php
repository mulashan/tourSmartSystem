@extends('templates.app')

@section('title', $user->name)

@section('content')
<div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>User Workspace</h1>
        <p class="text-muted mb-0">Manage user profile, branches, sub departments, approval permissions and workshop permissions.</p>
    </div>
    <a href="{{ route('users.list') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Users</a>
</div>

<section class="section">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @include('templates.users.partials.profile-header')

    <div class="row mt-4">
        <div class="col-lg-3 col-xl-2">
            @include('templates.users.partials.sidebar')
        </div>
        <div class="col-lg-9 col-xl-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div id="userTabContent">
                        @includeIf('templates.users.tabs.' . $tab)
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') {
        fn();
    } else {
        setTimeout(function () { whenJQueryReady(fn); }, 30);
    }
})(function () {
    $(function () {
        const $content = $('#userTabContent');
        const $sidebar = $('#userTabSidebar');
        const userId = $sidebar.data('user-id');

        $sidebar.on('click', '.js-user-tab', function (e) {
            e.preventDefault();

            const tab = $(this).data('tab');

            if ($(this).hasClass('active')) return;

            $sidebar.find('.js-user-tab').removeClass('active');
            $(this).addClass('active');

            $content.html('<div class="text-muted p-4">Loading...</div>');

            $.get(`/users/${userId}/tab/${tab}/content`)
                .done(html => {
                    $content.html(html);
                    window.history.pushState({}, '', `/users/${userId}/${tab}`);
                })
                .fail(() => {
                    $content.html('<div class="text-danger p-4">Failed to load this tab. Check the browser console / network tab for the actual error.</div>');
                });
        });
    });
});
</script>

@endsection

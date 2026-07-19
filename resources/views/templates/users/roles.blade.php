@extends('templates.app')

@php
    $userTypes = collect($userTypes ?? []);
    $selectedKeys = collect($selectedKeys ?? []);
    $userCounts = collect($userCounts ?? []);
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="page-hero">
        <div><h1>Roles & Permissions</h1><p>Assign sidebar menu access by user type.</p></div>
        @if($selectedType)
            <button form="rolesPermissionForm" class="nice-action"><i class="bi bi-check"></i> Save Changes</button>
        @endif
    </section>

    <div class="roles-grid">
        <aside class="side-stack">
            <section class="panel roles-list">
                <h2>User Types</h2>
                @foreach($userTypes as $type)
                    <a class="role-row {{ $selectedType && $selectedType->id === $type->id ? 'active' : '' }}" href="{{ route('users.roles', ['type' => $type->id]) }}">
                        <span class="role-icon"><i class="bi bi-shield-fill-check"></i></span>
                        <span><strong>{{ $type->privilege_name }}</strong><small>{{ $userCounts->get($type->id, 0) }} users</small></span>
                    </a>
                @endforeach
                @if($userTypes->isEmpty())
                    <div class="kv-row"><span>No user types added yet.</span></div>
                @endif
            </section>

            @if($selectedType)
                <section class="panel">
                    <h2>Role Details</h2>
                    <div class="kv-block"><span>Name</span><strong>{{ $selectedType->privilege_name }}</strong></div>
                    <div class="kv-block"><span>Description</span><strong>{{ $selectedType->privilege_desc }}</strong></div>
                    <div class="kv-block"><span>Access Level</span><strong>{{ $selectedType->access_level_id }}</strong></div>
                </section>
            @endif
        </aside>

        <section class="panel permissions-panel">
            @if($selectedType)
                <form id="rolesPermissionForm" action="{{ route('users.roles.store') }}" method="post">
                    @csrf
                    <input type="hidden" name="privilege_id" value="{{ $selectedType->id }}">
                    <div class="panel-head">
                        <div><h2>Menu Permissions</h2><p>Configure sidebar access for {{ $selectedType->privilege_name }}</p></div>
                        <button type="submit" class="nice-action">Save Changes</button>
                    </div>
                    <table class="nice-table permissions-table">
                        <thead><tr><th>Module</th><th>Menu</th><th>Route</th><th>Allow</th></tr></thead>
                        <tbody>
                            @foreach($permissionMenus as $group)
                                <tr class="module-row"><td colspan="4"><i class="bi bi-grid"></i> {{ $group['title'] }}</td></tr>
                                @foreach($group['items'] as $item)
                                    <tr>
                                        <td>{{ $group['title'] }}</td>
                                        <td>{{ $item['label'] }}</td>
                                        <td>{{ $item['url'] }}</td>
                                        <td><input type="checkbox" name="permissions[]" value="{{ $item['key'] }}" @checked($item['key'] === 'dashboard' || $selectedKeys->contains($item['key'])) @disabled($item['key'] === 'dashboard')></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </form>
            @else
                <div class="panel-head"><div><h2>Menu Permissions</h2><p>Create a user type first before assigning menus.</p></div></div>
            @endif
        </section>
    </div>
@endsection

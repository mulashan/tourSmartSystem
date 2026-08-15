<div class="list-group" id="userTabSidebar" data-user-id="{{ $user->id }}">
    @foreach([
        'edit-employee' => 'Edit Employee',
        'assign-branch' => 'Assign Branch',
        'assign-subdepartment' => 'Assign Sub Department',
        'assign-approval-permission' => 'Assign Approval Permission',
        'assign-system-permission' => 'System Permissions',
        'assign-workshop-permission' => 'Workshop Permissions',
    ] as $key => $label)
        <a href="{{ route('users.show', [$user, $key]) }}" data-tab="{{ $key }}" class="list-group-item list-group-item-action js-user-tab {{ $tab === $key ? 'active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

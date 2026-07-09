@extends('templates.app')

@php
    $people = [
        ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@example.com', 'role' => 'Admin', 'status' => 'Active', 'last' => 'Just now', 'joined' => 'Jan 15, 2024', 'tone' => 'teal'],
        ['name' => 'Michael Chen', 'email' => 'm.chen@example.com', 'role' => 'Manager', 'status' => 'Active', 'last' => '5 min ago', 'joined' => 'Feb 3, 2024', 'tone' => 'tan'],
        ['name' => 'Emily Rodriguez', 'email' => 'emily.r@example.com', 'role' => 'User', 'status' => 'Active', 'last' => '2 hours ago', 'joined' => 'Mar 12, 2024', 'tone' => 'slate'],
        ['name' => 'David Kim', 'email' => 'd.kim@example.com', 'role' => 'User', 'status' => 'Inactive', 'last' => '3 days ago', 'joined' => 'Jan 28, 2024', 'tone' => 'indigo'],
        ['name' => 'Jessica Taylor', 'email' => 'j.taylor@example.com', 'role' => 'Manager', 'status' => 'Active', 'last' => '1 hour ago', 'joined' => 'Dec 5, 2023', 'tone' => 'teal'],
        ['name' => 'Robert Martinez', 'email' => 'r.martinez@example.com', 'role' => 'User', 'status' => 'Active', 'last' => '30 min ago', 'joined' => 'Apr 18, 2024', 'tone' => 'tan'],
        ['name' => 'Amanda Wilson', 'email' => 'a.wilson@example.com', 'role' => 'User', 'status' => 'Pending', 'last' => 'Never', 'joined' => 'May 2, 2024', 'tone' => 'indigo'],
        ['name' => 'Chris Thompson', 'email' => 'c.thompson@example.com', 'role' => 'Admin', 'status' => 'Active', 'last' => '15 min ago', 'joined' => 'Nov 20, 2023', 'tone' => 'slate'],
    ];
@endphp

@section('content')
    @if($page === 'list')
        <section class="page-hero">
            <div><h1>People Directory</h1><p>Manage member access, lifecycle status, and team distribution from one control surface.</p></div>
            <div class="hero-actions"><button class="outline-btn"><i class="bi bi-download"></i> Export</button><button type="button" class="nice-action" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-plus"></i> Add User</button></div>
        </section>

        <div class="content-split">
            <section class="panel table-panel">
                <div class="tabs-toolbar">
                    <div class="soft-tabs"><button class="active">All <span>248</span></button><button>Active <span>186</span></button><button>Pending <span>24</span></button><button>Inactive <span>38</span></button></div>
                    <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search users, email, role..."><button><i class="bi bi-sliders"></i> Role</button></div>
                </div>
                <table class="nice-table directory-table">
                    <thead><tr><th><input type="checkbox"></th><th>User</th><th>Role</th><th>Status</th><th>Last Active</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($people as $person)
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><span class="tiny-avatar {{ $person['tone'] }}">{{ substr($person['name'], 0, 1) }}</span><strong>{{ $person['name'] }}</strong><small>{{ $person['email'] }}</small></td>
                                <td><span class="role-pill {{ strtolower($person['role']) }}"><i class="bi bi-person"></i> {{ $person['role'] }}</span></td>
                                <td><span class="state-dot {{ strtolower($person['status']) }}"></span>{{ $person['status'] }}</td>
                                <td>{{ $person['last'] }}</td>
                                <td>{{ $person['joined'] }}</td>
                                <td class="row-actions"><i class="bi bi-eye"></i><i class="bi bi-pencil"></i><i class="bi bi-three-dots"></i></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-footer"><span>Showing 1-8 of 248 users</span><div class="pager"><button disabled><i class="bi bi-chevron-left"></i></button><button class="active">1</button><button>2</button><button>3</button><button>...</button><button>31</button><button><i class="bi bi-chevron-right"></i></button></div></div>
            </section>

            <aside class="side-stack">
                <section class="panel"><h2>Directory Snapshot</h2><div class="snapshot-grid"><div><span>Total</span><strong>248</strong><small>+18 this month</small></div><div class="green-bg"><span>Active</span><strong>186</strong><small>75% engagement</small></div><div class="amber-bg"><span>Pending</span><strong>24</strong><small>Needs onboarding</small></div><div class="red-bg"><span>Inactive</span><strong>38</strong><small>Follow-up required</small></div></div></section>
                <section class="panel"><h2>Role Distribution</h2><div class="target-row redbar"><span>Admin</span><strong>34</strong><div><i style="width:34%"></i></div></div><div class="target-row amber"><span>Manager</span><strong>56</strong><div><i style="width:56%"></i></div></div><div class="target-row violet"><span>User</span><strong>158</strong><div><i style="width:78%"></i></div></div></section>
                <section class="panel"><div class="panel-head"><h2>Recently Added</h2><a href="#">View all</a></div>@foreach(['Amanda Wilson' => 'Invited 2 hours ago', 'Robert Martinez' => 'Joined today', 'Emily Rodriguez' => 'Activated yesterday'] as $name => $meta)<div class="mini-person"><span class="tiny-avatar teal">{{ substr($name,0,1) }}</span><span><strong>{{ $name }}</strong><small>{{ $meta }}</small></span></div>@endforeach</section>
            </aside>
        </div>

        <div class="modal fade add-user-modal" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title" id="addUserModalLabel">Add New User</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="#" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="add-user-grid">
                                <label>First Name
                                    <input type="text" name="first_name" placeholder="Enter first name">
                                </label>
                                <label>Last Name
                                    <input type="text" name="last_name" placeholder="Enter last name">
                                </label>
                                <label class="wide">Email Address
                                    <input type="email" name="email" placeholder="Enter email address">
                                </label>
                                <label class="wide">Role
                                    <select name="role">
                                        <option selected disabled>Select role...</option>
                                        <option>Admin</option>
                                        <option>Manager</option>
                                        <option>User</option>
                                    </select>
                                </label>
                                <label>Password
                                    <input type="password" name="password" placeholder="Enter password">
                                </label>
                                <label>Confirm Password
                                    <input type="password" name="password_confirmation" placeholder="Confirm password">
                                </label>
                                <label class="modal-check wide">
                                    <input type="checkbox" name="send_welcome" checked>
                                    <span>Send welcome email with login details</span>
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
    @endif

    @if($page === 'view')
        <section class="page-hero profile-hero">
            <div class="profile-head"><span class="big-avatar">J</span><div><h1>John Doe</h1><p>john.doe@example.com</p><div><span class="role-pill admin">Admin</span><span class="chip">#USR-001</span><span class="chip">Engineering</span></div></div></div>
            <div class="hero-actions"><button class="nice-action"><i class="bi bi-pencil"></i> Edit User</button><button class="danger-outline"><i class="bi bi-trash"></i> Delete</button></div>
        </section>
        <div class="content-split">
            <section class="panel"><h2>Operational Feed</h2>@foreach(['Logged in' => 'Chrome on Windows • New York, USA', 'Updated profile information' => 'Changed phone number and location', 'Enabled two-factor authentication' => 'Using authenticator app', 'Joined Engineering team' => 'Added by Chris Thompson', 'Completed 5 tasks in Project Alpha' => 'Sprint 12 milestone reached'] as $title => $meta)<div class="feed-row"><span class="dot green"></span><div><strong>{{ $title }}</strong><small>{{ $meta }}</small></div><em>{{ $loop->first ? 'Now' : ($loop->iteration * 2) . 'h ago' }}</em></div>@endforeach</section>
            <aside class="side-stack"><section class="panel"><h2>Account Posture</h2>@foreach(['Status' => 'Active', 'Email Verification' => 'Verified', '2FA' => 'Enabled', 'Last Login' => 'Just now', 'Risk Score' => 'Low', 'Manager' => 'Chris Thompson'] as $k => $v)<div class="kv-row"><span>{{ $k }}</span><strong>{{ $v }}</strong></div>@endforeach</section><section class="panel"><h2>Permission Surface</h2>@foreach(['Dashboard','Users','Roles','Settings','Reports'] as $item)<div class="kv-row"><span>{{ $item }}</span><i class="bi bi-check-circle-fill text-success"></i></div>@endforeach</section></aside>
            <section class="panel transactions-panel"><div class="panel-head"><h2>Project Coverage</h2><a href="#">Open workload</a></div><table class="nice-table"><thead><tr><th>Project</th><th>Role</th><th>Load</th><th>Progress</th></tr></thead><tbody><tr><td>Alpha Platform</td><td>Owner</td><td>High</td><td><span class="status ok">78%</span></td></tr><tr><td>Billing Upgrade</td><td>Reviewer</td><td>Medium</td><td><span class="status wait">56%</span></td></tr><tr><td>Mobile Analytics</td><td>Contributor</td><td>Low</td><td><span class="status wait">33%</span></td></tr></tbody></table></section>
        </div>
    @endif

    @if($page === 'edit')
        <section class="page-hero"><div><h1>User Edit Workspace</h1><p>Adjust identity, role scope, and security settings before publishing updates.</p></div><div class="hero-actions"><button class="outline-btn"><i class="bi bi-eye"></i> View Profile</button><button class="nice-action"><i class="bi bi-check"></i> Save Changes</button></div></section>
        <div class="edit-grid">
            <aside class="side-stack"><section class="panel identity-card"><h2>Identity Snapshot</h2><div class="identity-body"><span class="big-avatar">J</span><div><strong>John Doe</strong><small>john.doe@example.com</small><span class="chip">#USR-001</span></div></div><p>JPG, PNG or WEBP. Max 2MB.</p></section><section class="panel danger-zone"><h2><i class="bi bi-exclamation-triangle"></i> Danger Zone</h2><p>Once you delete a user, there is no going back. Please be certain.</p><button class="danger-outline w-100"><i class="bi bi-trash"></i> Delete User</button></section></aside>
            <section class="panel form-panel"><h2>Personal Information</h2><div class="form-grid">@foreach(['First Name *'=>'John','Last Name *'=>'Doe','Email Address *'=>'john.doe@example.com','Phone Number'=>'+1 (555) 123-4567','Date of Birth'=>'15 / 03 / 1990','Gender'=>'Male'] as $label=>$value)<label>{{ $label }}<input value="{{ $value }}"></label>@endforeach</div></section>
            <section class="panel form-panel"><h2>Work Information</h2><div class="form-grid">@foreach(['Department'=>'Engineering','Job Title'=>'Senior Developer','Manager'=>'Chris Thompson','Office Location'=>'New York, USA','Employee ID'=>'EMP-001','Start Date'=>'15 / 01 / 2024'] as $label=>$value)<label>{{ $label }}<input value="{{ $value }}"></label>@endforeach</div></section>
            <section class="panel form-panel"><h2>Role & Permissions</h2><div class="form-grid"><label>Role *<input value="Admin"><small>Admins have full access to all features.</small></label><label>Teams<select multiple><option>Engineering</option><option>Product</option><option>Design</option><option>Leadership</option></select><small>Hold Ctrl/Cmd to select multiple.</small></label></div></section>
        </div>
    @endif

    @if($page === 'profile')
        <section class="page-hero profile-hero"><div class="profile-head"><span class="big-avatar">K</span><div><span class="eyebrow">Product Design Lead</span><h1>Kevin Anderson</h1><p>Design systems, UX strategy, and product experience across core admin surfaces and internal tooling.</p><span class="chip">New York, USA</span><span class="chip">8 years experience</span><span class="chip">Joined January 2024</span></div></div><div class="hero-actions"><button class="nice-action"><i class="bi bi-pencil"></i> Edit Profile</button><button class="outline-btn"><i class="bi bi-share"></i> Share</button></div></section>
        <section class="metric-grid compact">@foreach(['Projects'=>'24','Tasks Closed'=>'182','Avg. Rating'=>'4.9','Team Size'=>'12'] as $k=>$v)<article class="panel metric-lite"><span>{{ $k }}</span><strong>{{ $v }}</strong><small>{{ $loop->first ? '4 active' : 'Peer reviews' }}</small></article>@endforeach</section>
        <div class="content-split"><aside class="side-stack"><section class="panel"><h2>Contact</h2>@foreach(['Email'=>'k.anderson@example.com','Phone'=>'(436) 486-3538','Company'=>'Lueilwitz, Wisoky and Leuschke','Website'=>'kevinanderson.design'] as $k=>$v)<div class="kv-block"><span>{{ $k }}</span><strong>{{ $v }}</strong></div>@endforeach</section><section class="panel"><h2>Skills</h2><div class="tag-cloud">@foreach(['Design Systems','Figma','UX Research','Interaction Design','Accessibility','HTML/CSS','Prototyping','Mentoring'] as $tag)<span>{{ $tag }}</span>@endforeach</div></section></aside><section class="panel"><h2>Work Overview</h2><p class="body-copy">Passionate product designer focused on accessible, high-performance interfaces with scalable design systems. I collaborate deeply with engineering and product teams to translate strategy into shipped experiences.</p><div class="info-grid">@foreach(['Department'=>'Product Design','Team'=>'Experience Platform','Manager'=>'Chris Thompson','Location'=>'New York HQ','Employment Type'=>'Full-time','Timezone'=>'UTC-5 (EST)'] as $k=>$v)<div><span>{{ $k }}</span><strong>{{ $v }}</strong></div>@endforeach</div></section></div>
    @endif

    @if(in_array($page, ['settings', 'notifications', 'activity']))
        @include('templates.users.settings-tabs', ['page' => $page])
    @endif

    @if($page === 'roles')
        <section class="page-hero"><div><h1>Roles & Permissions</h1><p>Home / Roles & Permissions</p></div><button class="nice-action"><i class="bi bi-plus"></i> Add Role</button></section>
        <div class="roles-grid"><aside class="side-stack"><section class="panel roles-list"><h2>Roles</h2>@foreach(['Administrator'=>'3 users','Manager'=>'8 users','Editor'=>'12 users','User'=>'156 users','Viewer'=>'45 users'] as $role=>$meta)<div class="role-row {{ $loop->first ? 'active' : '' }}"><span class="role-icon"><i class="bi bi-shield-fill-check"></i></span><span><strong>{{ $role }}</strong><small>{{ $meta }}</small></span></div>@endforeach</section><section class="panel"><h2>Role Details</h2><div class="kv-block"><span>Name</span><strong>Administrator</strong></div><div class="kv-block"><span>Description</span><strong>Full system access with all permissions enabled. Can manage users, roles, and system settings.</strong></div></section></aside><section class="panel permissions-panel"><div class="panel-head"><div><h2>Permissions Matrix</h2><p>Configure access for Administrator role</p></div><button class="nice-action">Save Changes</button></div><table class="nice-table permissions-table"><thead><tr><th>Module</th><th>View</th><th>Create</th><th>Edit</th><th>Delete</th><th>All</th></tr></thead><tbody>@foreach(['Dashboard'=>['Analytics Dashboard','Reports'],'User Management'=>['Users','Roles & Permissions','Teams'],'Content Management'=>['Pages','Blog Posts','Media Library'],'E-commerce'=>['Products','Orders','Customers','Coupons'],'System Settings'=>['General Settings','Email Templates','API Keys','Backup & Restore']] as $module=>$rows)<tr class="module-row"><td colspan="6"><i class="bi bi-grid"></i> {{ $module }}</td></tr>@foreach($rows as $row)<tr><td>{{ $row }}</td>@for($i=0;$i<5;$i++)<td><input type="checkbox" checked></td>@endfor</tr>@endforeach @endforeach</tbody></table></section></div>
    @endif
@endsection

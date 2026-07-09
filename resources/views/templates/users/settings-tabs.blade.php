@php
    $titles = [
        'settings' => ['Workspace Settings', 'Control profile, preferences, security, and account behavior from one place.'],
        'notifications' => ['Notification Center', 'Control channels, priorities, and alert routing across your workspace.'],
        'activity' => ['Activity & Sessions', 'Track account actions, sign-ins, and workspace security events.'],
    ];
@endphp

<section class="page-hero">
    <div><h1>{{ $titles[$page][0] }}</h1><p>{{ $titles[$page][1] }}</p></div>
    <div class="hero-actions"><button class="outline-btn"><i class="bi bi-arrow-clockwise"></i> {{ $page === 'activity' ? 'Export Log' : 'Reset' }}</button><button class="nice-action"><i class="bi bi-check"></i> {{ $page === 'activity' ? 'Filters' : 'Save All' }}</button></div>
</section>

<section class="metric-grid compact">
    @foreach(($page === 'activity' ? ['Logins'=>'24','Security'=>'3','Profile Changes'=>'8','Failed Attempts'=>'1'] : ($page === 'notifications' ? ['Unread'=>'12','Critical'=>'3','Muted Rules'=>'5','Last Sync'=>'1m ago'] : ['Profile Completion'=>'92%','2FA Status'=>'Enabled','Active Sessions'=>'3','Last Backup'=>'2h ago'])) as $k=>$v)
        <article class="panel metric-lite"><span>{{ $k }}</span><strong>{{ $v }}</strong><small>{{ $loop->iteration === 1 ? 'Needs review' : 'Protected' }}</small></article>
    @endforeach
</section>

<div class="settings-grid">
    <aside class="side-stack">
        <section class="panel tab-list">
            <a class="{{ $page === 'settings' ? 'active' : '' }}" href="{{ url('users/settings') }}"><i class="bi bi-sliders"></i><span><strong>General</strong><small>Profile and preferences</small></span></a>
            <a class="{{ $page === 'notifications' ? 'active' : '' }}" href="{{ url('users/notifications') }}"><i class="bi bi-bell"></i><span><strong>Notifications</strong><small>Alerts and channels</small></span></a>
            <a class="{{ $page === 'activity' ? 'active' : '' }}" href="{{ url('users/activity') }}"><i class="bi bi-clock-history"></i><span><strong>Activity Log</strong><small>History and sessions</small></span></a>
        </section>
        <section class="panel"><h2>{{ $page === 'activity' ? 'Risk Snapshot' : ($page === 'notifications' ? 'Delivery Channels' : 'Need help?') }}</h2>
            @foreach(['Email'=>'Transaction updates and important alerts','Push'=>'Mobile and desktop push notifications','In-App'=>'Live notifications inside dashboard'] as $k=>$v)
                <div class="toggle-row"><span><strong>{{ $k }}</strong><small>{{ $v }}</small></span><label class="switch"><input type="checkbox" checked><i></i></label></div>
            @endforeach
        </section>
    </aside>
    <section class="panel">
        @if($page === 'settings')
            <h2>Account Profile</h2><div class="form-grid">@foreach(['Full Name'=>'Kevin Anderson','Email Address'=>'k.anderson@example.com','Phone Number'=>'(436) 486-3538','Job Title'=>'Product Design Lead'] as $label=>$value)<label>{{ $label }}<input value="{{ $value }}"></label>@endforeach<label class="wide">Bio<textarea>Design leader focused on scalable systems, team enablement, and user-centered product outcomes.</textarea></label></div>
        @elseif($page === 'notifications')
            <div class="panel-head"><h2>Rule Preferences</h2><button class="outline-btn">Add Rule</button></div>@foreach(['Security Alerts'=>'High','Team Mentions'=>'Medium','Product Announcements'=>'Low','Billing Updates'=>'High'] as $rule=>$level)<div class="rule-row"><span><strong>{{ $rule }}</strong><small>Comments, assignments, and important updates</small></span><em class="{{ strtolower($level) }}">{{ $level }}</em><label class="switch"><input type="checkbox" checked><i></i></label></div>@endforeach
        @else
            <div class="panel-head"><h2>Recent Activity Timeline</h2><div class="segmented"><button class="active">All</button><button>Security</button><button>Profile</button></div></div>@foreach(['Password changed successfully'=>'Account password updated from trusted device.','New device login detected'=>'Chrome on Windows • New York, USA','Two-factor authentication enabled'=>'Authenticator app has been linked.','Profile details edited'=>'Phone and office location updated.','Failed sign-in attempt blocked'=>'Incorrect password from unrecognized location.'] as $title=>$meta)<div class="feed-row"><span class="dot {{ $loop->iteration % 2 ? 'green' : 'blue' }}"></span><div><strong>{{ $title }}</strong><small>{{ $meta }}</small></div><em>{{ $loop->first ? '2h ago' : 'Yesterday' }}</em></div>@endforeach
        @endif
    </section>
</div>

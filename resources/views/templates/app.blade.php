@php
    $institutionName = $institutionName ?? session('institution_name', 'ToursCompany');
    $userName = trim(session('first_name', 'John') . ' ' . session('last_name', 'Doe'));
    $menus = $menus ?? [];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $institutionName }} - Dashboard</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/niceadmin-local.css') }}">
    @yield('styles')
</head>
<body class="nice-dashboard-body">
    <div class="demo-bar">
        <div class="demo-brand"><span class="demo-mark">
            L
        </span> Leopard<span>Tours</span></div>
        <div class="demo-title">SmartSystem</div>
        <a href="#" class="demo-download">Enjoy</a>
    </div>

    <header class="nice-header">
        <div class="header-left">
            <button class="icon-btn sidebar-toggle" type="button"><i class="bi bi-list"></i></button>
            <a href="{{ url('dashboard') }}" class="brand-lockup">
                <span class="nice-logo-mark">N</span>
                <span>Tours</span>
            </a>
        </div>
        <div class="header-center">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search projects, invoices, users...">
                <kbd>/</kbd>
            </div>
        </div>
        <div class="header-actions">
            <div class="top-action">
                <button class="small-pill js-top-panel" data-panel="language-panel" type="button"><span class="flag-dot"></span> EN</button>
                <div id="language-panel" class="top-panel language-panel">
                    <h3>Select Language</h3>
                    <a class="active" href="#"><i class="bi bi-check-lg"></i><span class="flag-us"></span><strong>English</strong><small>United States</small></a>
                    <a href="#"><span class="flag-fr"></span><strong>Francais</strong><small>France</small></a>
                    <a href="#"><span class="flag-de"></span><strong>Deutsch</strong><small>Germany</small></a>
                    <a href="#"><span class="flag-es"></span><strong>Espanol</strong><small>Spain</small></a>
                </div>
            </div>
            <div class="top-action">
                <button class="icon-btn js-top-panel" data-panel="quick-panel" type="button"><i class="bi bi-grid-3x3-gap"></i></button>
                <div id="quick-panel" class="top-panel quick-panel">
                    <h3>Quick Access</h3>
                    <a class="quick-primary" href="#"><i class="bi bi-lightning-charge"></i><span><strong>Create New Workspace</strong><small>Launch projects, assign team, set timeline</small></span></a>
                    <div class="quick-grid">
                        <a href="{{ url('apps/calendar') }}"><i class="bi bi-calendar3"></i><strong>Calendar</strong><small>Events</small></a>
                        <a href="{{ url('apps/kanban-board') }}"><i class="bi bi-kanban"></i><strong>Kanban</strong><small>Boards</small></a>
                        <a href="{{ url('apps/chat') }}"><i class="bi bi-chat-dots"></i><strong>Chat</strong><small>Inbox</small></a>
                        <a href="{{ url('apps/email') }}"><i class="bi bi-envelope"></i><strong>Email</strong><small>Campaigns</small></a>
                        <a href="{{ url('apps/file-manager') }}"><i class="bi bi-folder2-open"></i><strong>Files</strong><small>Assets</small></a>
                        <a href="{{ url('apps/support-center') }}"><i class="bi bi-headset"></i><strong>Support</strong><small>Tickets</small></a>
                    </div>
                </div>
            </div>
            <div class="top-action">
                <button class="icon-btn has-badge js-top-panel" data-panel="notifications-panel" type="button"><i class="bi bi-bell"></i><span>4</span></button>
                <div id="notifications-panel" class="top-panel feed-panel">
                    <div class="panel-title"><div><h3>Notifications</h3><p>4 unread</p></div><a href="#">Mark all read</a></div>
                    <div class="notif-stats"><div><strong>7</strong><span>Today</span></div><div><strong>23</strong><span>This Week</span></div><div><strong>3</strong><span>Approvals</span></div></div>
                    <a class="feed-item" href="#"><i class="bi bi-rocket-takeoff blue-box"></i><span><strong>Deploy ready</strong><small>Sprint release passed QA validation.</small><em>5m ago</em></span></a>
                    <a class="feed-item" href="#"><span class="tiny-avatar tan">M</span><span><strong>Mia sent feedback</strong><small>Please review updated dashboard spacing.</small><em>21m ago</em></span></a>
                    <a class="feed-item" href="#"><i class="bi bi-exclamation-triangle amber-box"></i><span><strong>Storage alert</strong><small>Media bucket reached 81% usage.</small><em>58m ago</em></span></a>
                    <a class="feed-item" href="#"><i class="bi bi-check-circle green-box"></i><span><strong>Payment received</strong><small>Invoice #INV-3921 settled successfully.</small><em>2h ago</em></span></a>
                    <a class="panel-footer-link" href="{{ url('users/notifications') }}">Open notification center <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="top-action">
                <button class="icon-btn has-badge js-top-panel" data-panel="messages-panel" type="button"><i class="bi bi-chat-left-text"></i><span>5</span></button>
                <div id="messages-panel" class="top-panel feed-panel messages-panel">
                    <div class="panel-title"><div><h3>Messages</h3><p>5 unread</p></div><a href="{{ url('apps/chat') }}">Open chat</a></div>
                    <div class="message-tabs"><button class="active">Direct</button><button>Team</button><button>Clients</button></div>
                    <a class="feed-item unread" href="#"><span class="tiny-avatar tan">M</span><span><strong>Mia Rodriguez</strong><small>Can you review the analytics wireframe today?</small><em>2m ago</em></span></a>
                    <a class="feed-item unread" href="#"><span class="tiny-avatar teal">D</span><span><strong>Dev Channel</strong><small>Build passed. Ready for production deploy.</small><em>12m ago</em></span></a>
                    <a class="feed-item unread" href="#"><span class="tiny-avatar slate">S</span><span><strong>Sarah Kim</strong><small>Shared file: Q1-forecast-report.pdf</small><em>35m ago</em></span></a>
                    <a class="panel-footer-link" href="#">View all messages <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <button id="theme-toggle" class="icon-btn theme-toggle" type="button"><i class="bi bi-moon-stars"></i></button>
            <div class="top-action user-chip">
                <button class="user-button js-top-panel" data-panel="profile-panel" type="button">
                    <span class="avatar-dot">J</span>
                    <span>
                        <strong>{{ $userName ?: 'John Doe' }}</strong>
                        <small>Product Admin</small>
                    </span>
                </button>
                <div id="profile-panel" class="top-panel profile-panel">
                    <div class="profile-panel-head"><span class="big-avatar">J</span><span><strong>John Doe</strong><small>john.doe@example.com</small></span></div>
                    <a href="{{ url('users/profile') }}"><i class="bi bi-person"></i> My Profile</a>
                    <a href="{{ url('users/settings') }}"><i class="bi bi-sliders"></i> Preferences</a>
                    <a href="{{ url('users/activity') }}"><i class="bi bi-activity"></i> Activity Log</a>
                    <a href="#"><i class="bi bi-credit-card"></i> Billing</a>
                    <div class="profile-signout"><a href="{{ url('Logout') }}"><i class="bi bi-box-arrow-right"></i> Sign Out</a></div>
                </div>
            </div>
        </div>
    </header>

    <aside class="nice-sidebar">
        <nav>
            @foreach($menus as $group)
                <div class="menu-section">
                    @if(!empty($group['title']))
                        <div class="menu-title">{{ $group['title'] }}</div>
                    @endif
                    @foreach($group['items'] as $item)
                        @php
                            $hasChildren = !empty($item['children']);
                            $isOpen = !empty($item['open']) || collect($item['children'] ?? [])->contains(fn ($child) => !empty($child['active']));
                        @endphp
                        <a href="{{ ($item['url'] ?? '#') === '#' ? '#' : url($item['url']) }}" class="menu-link {{ !empty($item['active']) || $isOpen ? 'active' : '' }} {{ $hasChildren ? 'js-submenu-toggle' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <em>{{ $item['badge'] }}</em>
                            @elseif($hasChildren || !empty($item['chevron']))
                                <i class="bi bi-chevron-{{ $isOpen ? 'down' : 'right' }} chevron"></i>
                            @endif
                        </a>
                        @if($hasChildren)
                            <div class="submenu {{ $isOpen ? 'open' : '' }}">
                                @foreach($item['children'] as $child)
                                    @php
                                        $childHasChildren = !empty($child['children']);
                                        $childIsOpen = $childHasChildren && collect($child['children'])->contains(fn ($nested) => !empty($nested['active']));
                                    @endphp
                                    <a href="{{ ($child['url'] ?? '#') === '#' ? '#' : url($child['url']) }}" class="submenu-link {{ !empty($child['active']) ? 'active' : '' }} {{ $childHasChildren ? 'has-children js-submenu-toggle' : '' }}">
                                        <span class="submenu-dot"></span>
                                        <span>{{ $child['label'] }}</span>
                                        @if(!empty($child['chevron']) || $childHasChildren)
                                            <i class="bi bi-chevron-{{ $childIsOpen ? 'down' : 'right' }} chevron"></i>
                                        @endif
                                    </a>
                                    @if($childHasChildren)
                                        <div class="submenu nested {{ $childIsOpen ? 'open' : '' }}">
                                            @foreach($child['children'] as $nested)
                                                <a href="{{ ($nested['url'] ?? '#') === '#' ? '#' : url($nested['url']) }}" class="submenu-link {{ !empty($nested['active']) ? 'active' : '' }}">
                                                    <span class="submenu-dot"></span>
                                                    <span>{{ $nested['label'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-profile">
                <span class="avatar-dot">J</span>
                <span><strong>John Doe</strong><small>Product Admin</small></span>
                <button class="mini-icon"><i class="bi bi-gear"></i></button>
                <a href="{{ url('Logout') }}" class="mini-icon"><i class="bi bi-box-arrow-right"></i></a>
            </div>
            <div class="sidebar-actions">
                <button><i class="bi bi-headset"></i> Support Desk</button>
                <button><i class="bi bi-bell"></i> Alerts</button>
            </div>
        </div>
    </aside>

    <main class="nice-main">
        @yield('content')
    </main>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/niceadmin-local.js') }}"></script>
    @yield('scripts')
</body>
</html>

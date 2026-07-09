@extends('templates.app')

@php
    $title = $title ?? match ($page) {
        'calendar' => 'Calendar',
        'chat' => 'Chat',
        'file-manager' => 'File Manager',
        default => $title ?? 'Application',
    };
    $crumb = $crumb ?? $title;
@endphp

@section('content')
    <div class="app-page-title">
        <h1>{{ $title }}</h1>
        <nav>Home <span>/</span> Apps <span>/</span> {{ $crumb }}</nav>
    </div>

    @if($page === 'calendar')
        @php
            $weeks = [
                ['28', '29', '30', '1', '2', '3', '4'],
                ['5', '6', '7', '8', '9', '10', '11'],
                ['12', '13', '14', '15', '16', '17', '18'],
                ['19', '20', '21', '22', '23', '24', '25'],
                ['26', '27', '28', '29', '30', '31', '1'],
                ['2', '3', '4', '5', '6', '7', '8'],
            ];
            $events = [
                ['20', 'Team Standup Meeting', '09:00 - 09:30', 'blue'],
                ['21', 'Project Review', '14:00 - 15:00', 'green'],
                ['23', 'Sprint Deadline', 'All Day', 'red'],
                ['25', 'Client Presentation', '10:00 - 11:30', 'amber'],
            ];
        @endphp
        <div class="calendar-layout">
            <aside class="calendar-sidebar">
                <button class="nice-action w-100"><i class="bi bi-plus"></i> Create Event</button>
                <section class="panel calendar-mini">
                    <div class="panel-head"><h2>July 2026</h2><div><i class="bi bi-chevron-left"></i><i class="bi bi-chevron-right ms-3"></i></div></div>
                    <div class="mini-calendar-grid">
                        @foreach(['S','M','T','W','T','F','S'] as $day)<strong>{{ $day }}</strong>@endforeach
                        @foreach(['28','29','30','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','1','2','3','4','5','6','7','8'] as $day)
                            <span class="{{ $day === '8' ? 'active' : '' }} {{ in_array($loop->iteration, [1,2,3,35,36,37,38,39,40,41,42]) ? 'muted-day' : '' }}">{{ $day }}</span>
                        @endforeach
                    </div>
                </section>
                <section class="panel calendar-list">
                    <h2>Categories</h2>
                    @foreach([['Meetings', 5, 'blue'], ['Tasks', 8, 'green'], ['Reminders', 3, 'amber'], ['Deadlines', 2, 'red'], ['Personal', 4, 'sky']] as [$label, $count, $tone])
                        <div><span class="cal-square {{ $tone }}"></span>{{ $label }}<em>{{ $count }}</em></div>
                    @endforeach
                </section>
                <section class="panel upcoming-events">
                    <h2>Upcoming Events</h2>
                    @foreach($events as [$day, $name, $time, $tone])
                        <article class="{{ $tone }}"><strong>{{ $day }}<small>JAN</small></strong><span>{{ $name }}<small><i class="bi bi-clock"></i> {{ $time }}</small></span></article>
                    @endforeach
                </section>
            </aside>
            <section class="panel calendar-board">
                <div class="calendar-toolbar">
                    <div class="cal-left"><button class="outline-btn"><i class="bi bi-chevron-left"></i></button><button class="outline-btn">Today</button><button class="outline-btn"><i class="bi bi-chevron-right"></i></button><h2>Wednesday, July 8, 2026</h2></div>
                    <div class="segmented"><button>Day</button><button>Week</button><button class="active">Month</button></div>
                </div>
                <div class="month-grid">
                    @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)<div class="month-head">{{ $day }}</div>@endforeach
                    @foreach($weeks as $week)
                        @foreach($week as $day)
                            <div class="month-cell {{ $day === '8' ? 'today' : '' }} {{ ($loop->parent->first && in_array($day, ['28','29','30'])) || ($loop->parent->last && in_array($day, ['1','2','3','4','5','6','7','8'])) ? 'outside' : '' }}">
                                <span>{{ $day }}</span>
                                @if($day === '8')<strong>8</strong>@endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </section>
        </div>
    @elseif($page === 'chat')
        @php
            $threads = [
                ['Sarah Wilson', 'Sure, I\'ll send the files right away!', '2m ago', 'S', 'teal', true],
                ['Mike Johnson', 'The meeting has been rescheduled...', '15m ago', 'M', 'tan', false],
                ['Emily Davis', 'Did you see the latest design ...', '1h ago', 'E', 'slate', false],
                ['Design Team', 'Alex: Great work on the mockups e...', '2h ago', 'D', 'indigo', false],
                ['David Brown', 'Thanks for your help with the proje...', 'Yesterday', 'D', 'teal', false],
                ['Lisa Anderson', 'I\'ll review the document and get ba...', 'Yesterday', 'L', 'tan', false],
                ['Product Team', 'Sprint planning starts at 10 AM to...', '2 days ago', 'P', 'slate', false],
            ];
        @endphp
        <section class="panel chat-shell">
            <aside class="chat-list">
                <h2>Messages</h2>
                <div class="chat-search"><i class="bi bi-search"></i><input placeholder="Search conversations..."></div>
                <div class="chat-tabs"><button class="active">All</button><button>Unread <span>3</span></button><button>Groups</button></div>
                @foreach($threads as [$name, $text, $time, $initial, $tone, $active])
                    <a class="chat-thread {{ $active ? 'active' : '' }}" href="#">
                        <span class="tiny-avatar {{ $tone }}">{{ $initial }}</span>
                        <span><strong>{{ $name }}</strong><small>{{ $text }}</small></span>
                        <em>{{ $time }}</em>
                    </a>
                @endforeach
            </aside>
            <div class="chat-room">
                <header class="chat-room-head">
                    <div><span class="tiny-avatar teal">S</span><strong>Sarah Wilson</strong><small><span class="state-dot active"></span>Online</small></div>
                    <div class="chat-actions"><button><i class="bi bi-telephone"></i></button><button><i class="bi bi-camera-video"></i></button><button><i class="bi bi-info-circle"></i></button><button><i class="bi bi-three-dots-vertical"></i></button></div>
                </header>
                <div class="chat-date"><span></span><em>Today</em><span></span></div>
                <div class="messages-area">
                    <div class="message inbound"><span class="tiny-avatar teal">S</span><p>Hi there! How's the project going?</p><small>9:30 AM</small></div>
                    <div class="message outbound"><p>Hey Sarah! It's going well. Just finished the dashboard design.</p><small>Seen</small></div>
                    <div class="message inbound"><span class="tiny-avatar teal">S</span><p>That's great! Can you share the latest mockups?</p><small>9:35 AM</small></div>
                    <div class="message outbound"><p>Sure thing! Here are the files:</p><div class="file-bubble"><i class="bi bi-file-earmark-pdf"></i><span>Dashboard-Design-v2.pdf<small>2.4 MB</small></span><i class="bi bi-download"></i></div><small>Seen</small></div>
                    <div class="message inbound stack"><span class="tiny-avatar teal">S</span><p>Perfect! Let me take a look at these.</p><p>The design looks amazing! I love the new color scheme.</p><p>Just a few minor suggestions:<br>1. Could we make the sidebar a bit narrower?<br>2. The charts could use more contrast<br>3. Maybe add some icons to the navigation</p><small>10:15 AM</small></div>
                    <div class="message outbound"><p>Great feedback! I'll work on those changes today.</p><small>Seen</small></div>
                    <div class="message inbound"><span class="tiny-avatar teal">S</span><p>Sure, I'll send the files right away!</p><small>10:28 AM</small></div>
                </div>
                <footer class="chat-compose"><button><i class="bi bi-paperclip"></i></button><button><i class="bi bi-image"></i></button><input placeholder="Type a message..."><button><i class="bi bi-emoji-smile"></i></button><button class="send-btn"><i class="bi bi-send"></i></button></footer>
            </div>
        </section>
    @elseif($page === 'file-manager')
        @php
            $files = [
                ['hero-banner.jpg', '2.4 MB', 'bi-file-earmark-image', 'blue', true],
                ['Project Brief.docx', '156 KB', 'bi-file-earmark-word', 'blue', false],
                ['Annual Report.pdf', '4.8 MB', 'bi-file-earmark-pdf', 'red', false],
                ['Budget 2024.xlsx', '89 KB', 'bi-file-earmark-excel', 'green', false],
                ['team-photo.png', '1.8 MB', 'bi-file-earmark-image', 'amber', true],
                ['product-demo.mp4', '156 MB', 'bi-file-earmark-play', 'blue', false],
                ['assets-v2.zip', '45.2 MB', 'bi-file-earmark-zip', 'amber', false],
                ['script.js', '12 KB', 'bi-file-earmark-code', 'purple', false],
                ['podcast-ep12.mp3', '28.5 MB', 'bi-file-earmark-music', 'purple', false],
                ['meeting-notes.txt', '4 KB', 'bi-file-earmark-text', 'blue', false],
            ];
        @endphp
        <section class="panel file-shell">
            <aside class="file-nav">
                <button class="nice-action w-100"><i class="bi bi-cloud-upload"></i> Upload Files</button>
                <div class="file-nav-section">
                    <a class="active" href="#"><i class="bi bi-folder"></i>My Files</a>
                    <a href="#"><i class="bi bi-star"></i>Starred <em>5</em></a>
                    <a href="#"><i class="bi bi-clock-history"></i>Recent</a>
                    <a href="#"><i class="bi bi-people"></i>Shared <em class="green-bg">12</em></a>
                </div>
                <div class="file-nav-section"><h3>Categories</h3><a href="#"><i class="bi bi-file-earmark-text"></i>Documents</a><a href="#"><i class="bi bi-file-earmark-image"></i>Images</a><a href="#"><i class="bi bi-film"></i>Videos</a><a href="#"><i class="bi bi-music-note-beamed"></i>Audio</a></div>
                <div class="file-storage"><div><span>Storage</span><strong>67.4 GB / 100 GB</strong></div><p><i style="width:67%"></i></p><small>32.6 GB free</small></div>
            </aside>
            <div class="file-main">
                <div class="file-toolbar">
                    <div class="breadcrumb-lite"><i class="bi bi-house"></i><span>My Files</span><i class="bi bi-chevron-right"></i><strong>Projects</strong></div>
                    <div class="toolbar-search"><i class="bi bi-search"></i><input placeholder="Search files..."></div>
                    <button class="outline-btn"><i class="bi bi-grid-3x3-gap"></i></button><button class="outline-btn"><i class="bi bi-list-ul"></i></button><button class="outline-btn"><i class="bi bi-sort-down"></i> Sort</button><button class="nice-action"><i class="bi bi-folder-plus"></i> New Folder</button>
                </div>
                <h2>Quick Access</h2>
                <div class="quick-access-row">
                    @foreach([['Documents','1,234 files','bi-file-earmark-text','blue'],['Images','5,678 files','bi-image','green'],['Videos','89 files','bi-film','sky'],['Music','456 files','bi-music-note-beamed','purple']] as [$label, $meta, $icon, $tone])
                        <article><i class="bi {{ $icon }} {{ $tone }}"></i><span><strong>{{ $label }}</strong><small>{{ $meta }}</small></span></article>
                    @endforeach
                </div>
                <h2>Folders</h2>
                <div class="folder-grid">
                    @foreach([['Design Assets','24 items'],['Development','156 items'],['Marketing','42 items'],['Reports','18 items'],['Archives','89 items']] as [$folder, $meta])
                        <article class="{{ $folder === 'Archives' ? 'selected' : '' }}"><i class="bi bi-folder-fill"></i><strong>{{ $folder }}</strong><small>{{ $meta }}</small></article>
                    @endforeach
                </div>
                <h2>Files</h2>
                <div class="file-grid">
                    @foreach($files as [$name, $size, $icon, $tone, $thumb])
                        <article>
                            @if($thumb)
                                <div class="file-thumb"></div>
                            @else
                                <i class="bi {{ $icon }} {{ $tone }}"></i>
                            @endif
                            <strong>{{ $name }}</strong>
                            <small>{{ $size }}</small>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="page-hero">
            <div><h1>{{ $title }}</h1><p>This menu is connected and ready for its full page content.</p></div>
            <a class="nice-action" href="{{ url('dashboard') }}"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </section>
        <section class="panel placeholder-panel">
            <i class="bi bi-window-sidebar"></i>
            <h2>{{ $title }}</h2>
            <p>Route imeunganishwa kwenye sidebar. Unaweza kuongeza content halisi hapa baadaye.</p>
        </section>
    @endif
@endsection

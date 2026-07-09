<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('templates.login');
})->name('login');

Route::get('/register', function () {
    return view('templates.register');
})->name('register');

function niceMenus(string $active = 'dashboard'): array
{
    $is = fn (string $key): bool => $active === $key;

    return [
        [
            'title' => '',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'bi-grid', 'url' => 'dashboard', 'active' => $is('dashboard'), 'badge' => 'Home'],
                ['label' => 'Dashboards', 'icon' => 'bi-speedometer2', 'url' => '#', 'badge' => '6'],
                [
                    'label' => 'Users',
                    'icon' => 'bi-people',
                    'url' => '#',
                    'open' => str_starts_with($active, 'users.'),
                    'children' => [
                        ['label' => 'Users List', 'url' => 'users', 'active' => $is('users.list')],
                        ['label' => 'User View', 'url' => 'users/view', 'active' => $is('users.view')],
                        ['label' => 'User Edit', 'url' => 'users/edit', 'active' => $is('users.edit')],
                        ['label' => 'Profile', 'url' => 'users/profile', 'active' => $is('users.profile')],
                        [
                            'label' => 'Settings',
                            'url' => '#',
                            'active' => $is('users.settings') || $is('users.notifications') || $is('users.activity'),
                            'chevron' => true,
                            'children' => [
                                ['label' => 'Account', 'url' => 'users/settings', 'active' => $is('users.settings')],
                                ['label' => 'Notifications', 'url' => 'users/notifications', 'active' => $is('users.notifications')],
                                ['label' => 'Activity', 'url' => 'users/activity', 'active' => $is('users.activity')],
                            ],
                        ],
                        ['label' => 'Roles & Permissions', 'url' => 'users/roles-permissions', 'active' => $is('users.roles')],
                    ],
                ],
                ['label' => 'Authentication', 'icon' => 'bi-shield-check', 'url' => '#', 'badge' => '7'],
            ],
        ],
        [
            'title' => 'Productivity Apps',
            'items' => [
                ['label' => 'Calendar', 'icon' => 'bi-calendar4-week', 'url' => 'apps/calendar', 'active' => $is('apps.calendar')],
                ['label' => 'Kanban Board', 'icon' => 'bi-kanban', 'url' => 'apps/kanban-board', 'active' => $is('apps.kanban')],
                ['label' => 'Chat', 'icon' => 'bi-chat-left-dots', 'url' => 'apps/chat', 'active' => $is('apps.chat')],
                ['label' => 'Contacts', 'icon' => 'bi-person-lines-fill', 'url' => 'apps/contacts', 'active' => $is('apps.contacts')],
                ['label' => 'File Manager', 'icon' => 'bi-folder2-open', 'url' => 'apps/file-manager', 'active' => $is('apps.files')],
                ['label' => 'Email', 'icon' => 'bi-envelope', 'url' => 'apps/email', 'active' => $is('apps.email')],
                ['label' => 'Todo List', 'icon' => 'bi-check2-all', 'url' => 'apps/todo-list', 'active' => $is('apps.todo')],
                ['label' => 'Support Center', 'icon' => 'bi-headset', 'url' => 'apps/support-center', 'active' => $is('apps.support')],
            ],
        ],
        [
            'title' => 'Interface',
            'items' => [
                ['label' => 'Components', 'icon' => 'bi-box', 'url' => '#', 'chevron' => true],
                ['label' => 'Widgets', 'icon' => 'bi-layers', 'url' => '#', 'chevron' => true],
                ['label' => 'Forms', 'icon' => 'bi-ui-checks-grid', 'url' => '#', 'chevron' => true],
                ['label' => 'Tables', 'icon' => 'bi-table', 'url' => '#', 'chevron' => true],
                ['label' => 'Charts', 'icon' => 'bi-bar-chart', 'url' => '#', 'chevron' => true],
                ['label' => 'Icons', 'icon' => 'bi-diamond', 'url' => '#', 'chevron' => true],
            ],
        ],
        [
            'title' => 'Utility Pages',
            'items' => [
                ['label' => 'Contact', 'icon' => 'bi-send', 'url' => 'utility/contact', 'active' => $is('utility.contact')],
                [
                    'label' => 'Invoices',
                    'icon' => 'bi-receipt',
                    'url' => '#',
                    'open' => str_starts_with($active, 'utility.invoices.'),
                    'children' => [
                        ['label' => 'Invoice List', 'url' => 'utility/invoices', 'active' => $is('utility.invoices.list')],
                        ['label' => 'Invoice View', 'url' => 'utility/invoices/view', 'active' => $is('utility.invoices.view')],
                    ],
                ],
                ['label' => 'Pricing', 'icon' => 'bi-tag', 'url' => 'utility/pricing', 'active' => $is('utility.pricing')],
                ['label' => 'FAQ', 'icon' => 'bi-question-circle', 'url' => 'utility/faq', 'active' => $is('utility.faq')],
                ['label' => 'Error Pages', 'icon' => 'bi-exclamation-triangle', 'url' => 'utility/error-pages', 'active' => $is('utility.errors'), 'chevron' => true],
                ['label' => 'Timeline', 'icon' => 'bi-clock-history', 'url' => 'utility/timeline', 'active' => $is('utility.timeline')],
                ['label' => 'Search Results', 'icon' => 'bi-search', 'url' => 'utility/search-results', 'active' => $is('utility.search')],
                ['label' => 'Blank Page', 'icon' => 'bi-file-earmark', 'url' => 'utility/blank-page', 'active' => $is('utility.blank')],
            ],
        ],
    ];
}

function nicePage(string $view, string $active, array $data = [])
{
    if (! session('logged_in') && ! auth()->check()) {
        return redirect('/login');
    }

    return view($view, array_merge([
        'institutionName' => session('institution_name', 'NiceAdmin'),
        'menus' => niceMenus($active),
        'activePage' => $active,
    ], $data));
}

Route::post('/validate', function (Request $request) {
    $request->session()->put([
        'logged_in' => true,
        'user_id' => 1,
        'db_id' => 1,
        'institution_name' => 'NiceAdmin',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'user_photo' => '',
    ]);

    return redirect($request->input('redirect') ?: '/dashboard');
});

Route::get('/dashboard', function () {
    $calendarMonths = collect(range(1, 12))->map(function ($month) {
        return [
            'month_number' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
            'events' => $month === (int) date('n')
                ? [(object) ['id' => 1, 'event_date' => date('Y-m-d'), 'event_description' => 'Demo school calendar event']]
                : [],
        ];
    })->all();

    return nicePage('templates.dashboard', 'dashboard', [
        'releaseHistory' => [
            [
                'version' => '1.0.0',
                'released_at' => now()->format('Y-m-d H:i'),
                'title' => 'Laravel template ready',
                'changes' => ['CI4 template converted to Laravel Blade.', 'Demo routes added for login and dashboard.'],
            ],
        ],
        'copyrightName' => 'MHI',
        'copyrightUrl' => '#',
        'dashboardCalendarMonths' => $calendarMonths,
        'dashboardCurrentMonth' => $calendarMonths[(int) date('n') - 1],
        'dashboardCalendarYear' => (int) date('Y'),
    ]);
});

Route::get('/users', fn () => nicePage('templates.users.page', 'users.list', ['page' => 'list']));
Route::get('/users/view', fn () => nicePage('templates.users.page', 'users.view', ['page' => 'view']));
Route::get('/users/edit', fn () => nicePage('templates.users.page', 'users.edit', ['page' => 'edit']));
Route::get('/users/profile', fn () => nicePage('templates.users.page', 'users.profile', ['page' => 'profile']));
Route::get('/users/settings', fn () => nicePage('templates.users.page', 'users.settings', ['page' => 'settings']));
Route::get('/users/notifications', fn () => nicePage('templates.users.page', 'users.notifications', ['page' => 'notifications']));
Route::get('/users/activity', fn () => nicePage('templates.users.page', 'users.activity', ['page' => 'activity']));
Route::get('/users/roles-permissions', fn () => nicePage('templates.users.page', 'users.roles', ['page' => 'roles']));

Route::get('/apps/calendar', fn () => nicePage('templates.apps.page', 'apps.calendar', ['page' => 'calendar']));
Route::get('/apps/chat', fn () => nicePage('templates.apps.page', 'apps.chat', ['page' => 'chat']));
Route::get('/apps/file-manager', fn () => nicePage('templates.apps.page', 'apps.files', ['page' => 'file-manager']));
Route::get('/apps/kanban-board', fn () => nicePage('templates.apps.page', 'apps.kanban', ['page' => 'placeholder', 'title' => 'Kanban Board', 'crumb' => 'Kanban Board']));
Route::get('/apps/contacts', fn () => nicePage('templates.apps.page', 'apps.contacts', ['page' => 'placeholder', 'title' => 'Contacts', 'crumb' => 'Contacts']));
Route::get('/apps/email', fn () => nicePage('templates.apps.page', 'apps.email', ['page' => 'placeholder', 'title' => 'Email', 'crumb' => 'Email']));
Route::get('/apps/todo-list', fn () => nicePage('templates.apps.page', 'apps.todo', ['page' => 'placeholder', 'title' => 'Todo List', 'crumb' => 'Todo List']));
Route::get('/apps/support-center', fn () => nicePage('templates.apps.page', 'apps.support', ['page' => 'placeholder', 'title' => 'Support Center', 'crumb' => 'Support Center']));

Route::get('/utility/invoices', fn () => nicePage('templates.utility.page', 'utility.invoices.list', ['page' => 'invoice-list']));
Route::get('/utility/invoices/view', fn () => nicePage('templates.utility.page', 'utility.invoices.view', ['page' => 'invoice-view']));
Route::get('/utility/pricing', fn () => nicePage('templates.utility.page', 'utility.pricing', ['page' => 'pricing']));
Route::get('/utility/contact', fn () => nicePage('templates.utility.page', 'utility.contact', ['page' => 'placeholder', 'title' => 'Contact']));
Route::get('/utility/faq', fn () => nicePage('templates.utility.page', 'utility.faq', ['page' => 'placeholder', 'title' => 'FAQ']));
Route::get('/utility/error-pages', fn () => nicePage('templates.utility.page', 'utility.errors', ['page' => 'placeholder', 'title' => 'Error Pages']));
Route::get('/utility/timeline', fn () => nicePage('templates.utility.page', 'utility.timeline', ['page' => 'placeholder', 'title' => 'Timeline']));
Route::get('/utility/search-results', fn () => nicePage('templates.utility.page', 'utility.search', ['page' => 'placeholder', 'title' => 'Search Results']));
Route::get('/utility/blank-page', fn () => nicePage('templates.utility.page', 'utility.blank', ['page' => 'placeholder', 'title' => 'Blank Page']));

Route::get('/Logout', function (Request $request) {
    $request->session()->flush();

    return redirect('/login');
});

Route::get('/Logout2', function () {
    return redirect('/login');
});

Route::get('/load_privacy', function () {
    return '<p class="mb-0">Privacy policy content goes here.</p>';
});

Route::get('/profile_edit/{user}', function ($user) {
    return '<div class="p-3"><h5>Profile</h5><p>User #' . e($user) . '</p></div>';
});

Route::post('/system/releases/seen', function () {
    return response()->json(['ok' => true]);
});

Route::post('/system/releases/new', function () {
    return response()->json([['seen' => 1]]);
});

Route::post('/view_school_calendar', function (Request $request) {
    return '<div><strong>Demo event</strong><p class="mb-0">Calendar event #' . e($request->input('id')) . '</p></div>';
});

Route::post('/dashboard_school_calendar_widget', function () {
    $calendarMonths = collect(range(1, 12))->map(function ($month) {
        return [
            'month_number' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
            'events' => $month === (int) date('n')
                ? [(object) ['id' => 1, 'event_date' => date('Y-m-d'), 'event_description' => 'Demo school calendar event']]
                : [],
        ];
    })->all();

    return view('templates.dashboard_school_calendar_widget', [
        'dashboardCalendarMonths' => $calendarMonths,
        'dashboardCurrentMonth' => $calendarMonths[(int) date('n') - 1],
        'dashboardCalendarYear' => (int) date('Y'),
    ]);
});

Route::get('/company/load-content-view/{year}', function ($year) {
    return view('templates.dashboard_cards', ['year' => $year]);
});

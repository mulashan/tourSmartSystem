<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return redirect('/login');
    }

    public function login()
    {
        return view('templates.login');
    }

    public function register()
    {
        return view('templates.register');
    }

    public function dashboard()
    {
        $calendarMonths = $this->calendarMonths();

        return $this->nicePage('templates.dashboard', 'dashboard', [
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
    }

    public function userPage(string $page, string $active)
    {
        return $this->nicePage('templates.users.page', $active, ['page' => $page]);
    }

    public function userView()
    {
        return $this->userPage('view', 'users.view');
    }

    public function userEdit()
    {
        return $this->userPage('edit', 'users.edit');
    }

    public function userProfile()
    {
        return $this->userPage('profile', 'users.profile');
    }

    public function userSettings()
    {
        return $this->userPage('settings', 'users.settings');
    }

    public function userNotifications()
    {
        return $this->userPage('notifications', 'users.notifications');
    }

    public function userActivity()
    {
        return $this->userPage('activity', 'users.activity');
    }

    public function appPage(string $page, string $active, array $data = [])
    {
        return $this->nicePage('templates.apps.page', $active, array_merge(['page' => $page], $data));
    }

    public function appCalendar()
    {
        return $this->appPage('calendar', 'apps.calendar');
    }

    public function appChat()
    {
        return $this->appPage('chat', 'apps.chat');
    }

    public function appFileManager()
    {
        return $this->appPage('file-manager', 'apps.files');
    }

    public function appKanban()
    {
        return $this->appPage('placeholder', 'apps.kanban', ['title' => 'Kanban Board', 'crumb' => 'Kanban Board']);
    }

    public function appContacts()
    {
        return $this->appPage('placeholder', 'apps.contacts', ['title' => 'Contacts', 'crumb' => 'Contacts']);
    }

    public function appEmail()
    {
        return $this->appPage('placeholder', 'apps.email', ['title' => 'Email', 'crumb' => 'Email']);
    }

    public function appTodo()
    {
        return $this->appPage('placeholder', 'apps.todo', ['title' => 'Todo List', 'crumb' => 'Todo List']);
    }

    public function appSupport()
    {
        return $this->appPage('placeholder', 'apps.support', ['title' => 'Support Center', 'crumb' => 'Support Center']);
    }

    public function utilityPage(string $page, string $active, array $data = [])
    {
        return $this->nicePage('templates.utility.page', $active, array_merge(['page' => $page], $data));
    }

    public function utilityInvoiceList()
    {
        return $this->utilityPage('invoice-list', 'utility.invoices.list');
    }

    public function utilityInvoiceView()
    {
        return $this->utilityPage('invoice-view', 'utility.invoices.view');
    }

    public function utilityPricing()
    {
        return $this->utilityPage('pricing', 'utility.pricing');
    }

    public function utilityContact()
    {
        return $this->utilityPage('placeholder', 'utility.contact', ['title' => 'Contact']);
    }

    public function utilityFaq()
    {
        return $this->utilityPage('placeholder', 'utility.faq', ['title' => 'FAQ']);
    }

    public function utilityErrorPages()
    {
        return $this->utilityPage('placeholder', 'utility.errors', ['title' => 'Error Pages']);
    }

    public function utilityTimeline()
    {
        return $this->utilityPage('placeholder', 'utility.timeline', ['title' => 'Timeline']);
    }

    public function utilitySearchResults()
    {
        return $this->utilityPage('placeholder', 'utility.search', ['title' => 'Search Results']);
    }

    public function utilityBlankPage()
    {
        return $this->utilityPage('placeholder', 'utility.blank', ['title' => 'Blank Page']);
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect('/login');
    }

    public function logoutRedirect()
    {
        return redirect('/login');
    }

    public function loadPrivacy()
    {
        return '<p class="mb-0">Privacy policy content goes here.</p>';
    }

    public function profileEdit($user)
    {
        return '<div class="p-3"><h5>Profile</h5><p>User #' . e($user) . '</p></div>';
    }

    public function releasesSeen()
    {
        return response()->json(['ok' => true]);
    }

    public function releasesNew()
    {
        return response()->json([['seen' => 1]]);
    }

    public function viewSchoolCalendar(Request $request)
    {
        return '<div><strong>Demo event</strong><p class="mb-0">Calendar event #' . e($request->input('id')) . '</p></div>';
    }

    public function dashboardSchoolCalendarWidget()
    {
        $calendarMonths = $this->calendarMonths();

        return view('templates.dashboard_school_calendar_widget', [
            'dashboardCalendarMonths' => $calendarMonths,
            'dashboardCurrentMonth' => $calendarMonths[(int) date('n') - 1],
            'dashboardCalendarYear' => (int) date('Y'),
        ]);
    }

    public function companyContentView($year)
    {
        return view('templates.dashboard_cards', ['year' => $year]);
    }

    private function calendarMonths(): array
    {
        return collect(range(1, 12))->map(function ($month) {
            return [
                'month_number' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'events' => $month === (int) date('n')
                    ? [(object) ['id' => 1, 'event_date' => date('Y-m-d'), 'event_description' => 'Demo school calendar event']]
                    : [],
            ];
        })->all();
    }
}

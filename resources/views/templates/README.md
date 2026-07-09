# Laravel Blade Template Conversion

These files are Laravel Blade versions of the CodeIgniter 4 template files under `Views/templates`.

## Main files

- `app.blade.php` replaces the CI4 `header.php` + `footer.php` wrapper.
- `login.blade.php` replaces `Views/templates/login.php`.
- `dashboard.blade.php` replaces `Views/templates/contents.php`.
- `dashboard_school_calendar_widget.blade.php` replaces `Views/templates/dashboard_school_calendar_widget.php`.

## Data the Laravel controller should pass

The old CI4 templates queried models directly inside views. In Laravel, pass those values from controllers or view composers:

- `$institutionName`
- `$companyLogo`
- `$menus`, with nested `sub_menus` for collapsed menu groups
- `$releaseHistory` and `$currentRelease`
- `$copyrightName` and `$copyrightUrl`
- `$approvalCount` and `$showApprovals`
- `$howToPayImages`
- `$dashboardCalendarMonths`, `$dashboardCurrentMonth`, `$dashboardCalendarYear`

## Route names/URLs used by the templates

Add Laravel routes matching these URLs or adjust the template URLs:

- `login`
- `validate`
- `dashboard`
- `load_privacy`
- `profile_edit/{user}`
- `Logout`
- `Logout2`
- `system/releases/seen`
- `system/releases/new`
- `view_school_calendar`
- `dashboard_school_calendar_widget`
- `company/load-content-view/{year}`

Keep public assets under Laravel's `public/` folder so calls like `asset('assets/css/style.min.css')` resolve correctly.

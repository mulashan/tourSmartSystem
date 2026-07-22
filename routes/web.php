<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Settings\BranchController;
use App\Http\Controllers\Settings\DepartmentController;
use App\Http\Controllers\Settings\DepartmentNatureController;
use App\Http\Controllers\Settings\DesignationController;
use App\Http\Controllers\Settings\EmployeeJobCodeController;
use App\Http\Controllers\Settings\EmployeeUnitController;
use App\Http\Controllers\Settings\EmployeeController;
use App\Http\Controllers\Settings\HrEmploymentTypeController;
use App\Http\Controllers\Settings\JobTitleController;
use App\Http\Controllers\Users\RolesPermissionController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Users\UserTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home']);

Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/register', [PageController::class, 'register'])->name('register');
Route::post('/validate', [LoginController::class, 'validateLogin']);

Route::get('/dashboard', [PageController::class, 'dashboard']);

Route::get('/users', [UserController::class, 'index'])->name('users.list');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/types', [UserTypeController::class, 'index'])->name('users.types');
Route::post('/users/types', [UserTypeController::class, 'store'])->name('users.types.store');
Route::get('/users/view', [PageController::class, 'userView']);
Route::get('/users/edit', [PageController::class, 'userEdit']);
Route::get('/users/profile', [PageController::class, 'userProfile']);
Route::get('/users/settings', [PageController::class, 'userSettings']);
Route::get('/users/notifications', [PageController::class, 'userNotifications']);
Route::get('/users/activity', [PageController::class, 'userActivity']);
Route::get('/users/roles-permissions', [RolesPermissionController::class, 'index'])->name('users.roles');
Route::post('/users/roles-permissions', [RolesPermissionController::class, 'store'])->name('users.roles.store');

Route::get('/settings/branch', [BranchController::class, 'index'])->name('settings.branch');
Route::post('/settings/branch', [BranchController::class, 'store'])->name('settings.branch.store');
Route::get('/settings/employee-job-codes', [EmployeeJobCodeController::class, 'index'])->name('settings.employee-job-codes');
Route::post('/settings/employee-job-codes', [EmployeeJobCodeController::class, 'store'])->name('settings.employee-job-codes.store');
Route::get('/settings/hr-employment-types', [HrEmploymentTypeController::class, 'index'])->name('settings.hr-employment-types');
Route::post('/settings/hr-employment-types', [HrEmploymentTypeController::class, 'store'])->name('settings.hr-employment-types.store');
Route::get('/settings/job-titles', [JobTitleController::class, 'index'])->name('settings.job-titles');
Route::post('/settings/job-titles', [JobTitleController::class, 'store'])->name('settings.job-titles.store');
Route::get('/settings/department-natures', [DepartmentNatureController::class, 'index'])->name('settings.department-natures');
Route::post('/settings/department-natures', [DepartmentNatureController::class, 'store'])->name('settings.department-natures.store');
Route::get('/settings/designations', [DesignationController::class, 'index'])->name('settings.designations');
Route::post('/settings/designations', [DesignationController::class, 'store'])->name('settings.designations.store');
Route::get('/settings/employee-units', [EmployeeUnitController::class, 'index'])->name('settings.employee-units');
Route::post('/settings/employee-units', [EmployeeUnitController::class, 'store'])->name('settings.employee-units.store');

Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.list');
Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.list');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');

Route::get('/apps/calendar', [PageController::class, 'appCalendar']);
Route::get('/apps/chat', [PageController::class, 'appChat']);
Route::get('/apps/file-manager', [PageController::class, 'appFileManager']);
Route::get('/apps/kanban-board', [PageController::class, 'appKanban']);
Route::get('/apps/contacts', [PageController::class, 'appContacts']);
Route::get('/apps/email', [PageController::class, 'appEmail']);
Route::get('/apps/todo-list', [PageController::class, 'appTodo']);
Route::get('/apps/support-center', [PageController::class, 'appSupport']);

Route::get('/utility/invoices', [PageController::class, 'utilityInvoiceList']);
Route::get('/utility/invoices/view', [PageController::class, 'utilityInvoiceView']);
Route::get('/utility/pricing', [PageController::class, 'utilityPricing']);
Route::get('/utility/contact', [PageController::class, 'utilityContact']);
Route::get('/utility/faq', [PageController::class, 'utilityFaq']);
Route::get('/utility/error-pages', [PageController::class, 'utilityErrorPages']);
Route::get('/utility/timeline', [PageController::class, 'utilityTimeline']);
Route::get('/utility/search-results', [PageController::class, 'utilitySearchResults']);
Route::get('/utility/blank-page', [PageController::class, 'utilityBlankPage']);

Route::get('/Logout', [PageController::class, 'logout']);
Route::get('/Logout2', [PageController::class, 'logoutRedirect']);
Route::get('/load_privacy', [PageController::class, 'loadPrivacy']);
Route::get('/profile_edit/{user}', [PageController::class, 'profileEdit']);

Route::post('/system/releases/seen', [PageController::class, 'releasesSeen']);
Route::post('/system/releases/new', [PageController::class, 'releasesNew']);
Route::post('/view_school_calendar', [PageController::class, 'viewSchoolCalendar']);
Route::post('/dashboard_school_calendar_widget', [PageController::class, 'dashboardSchoolCalendarWidget']);
Route::get('/company/load-content-view/{year}', [PageController::class, 'companyContentView']);

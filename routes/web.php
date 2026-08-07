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
use App\Http\Controllers\Settings\Other_SettingsController;
use App\Http\Controllers\Settings\BranchDepartmentController;
use App\Http\Controllers\Settings\SubdepartmentController;
use App\Http\Controllers\StorageSupplies\ItemController;
use App\Http\Controllers\Settings\SupplierController;
use App\Http\Controllers\StorageSupplies\SubdepartmentSessionController;
use App\Http\Controllers\Users\UserSubdepartmentController;
use App\Http\Controllers\StorageSupplies\StoreOrderController;
use App\Http\Controllers\Procurement\ProcurementController;
use App\Http\Controllers\Users\UserWorkspaceController;
use App\Http\Controllers\Auth\BranchSessionController;
use App\Http\Controllers\StorageSupplies\GrnController;
use App\Http\Controllers\Workshop\WorkshopController;

Route::get('/', [PageController::class, 'home']);

Route::get('/login', [PageController::class, 'login'])->name('login');
Route::get('/register', [PageController::class, 'register'])->name('register');
Route::post('/validate', [LoginController::class, 'validateLogin']);
/* added */
Route::get('/login/select-branch', [LoginController::class, 'selectBranchForm'])->name('login.select-branch');
Route::post('/login/select-branch', [LoginController::class, 'selectBranchSubmit'])->name('login.select-branch.submit');
Route::get('/branch/change', [BranchSessionController::class, 'form'])->name('branch.change');
Route::post('/branch/change', [BranchSessionController::class, 'update'])->name('branch.change.submit');

Route::get('/dashboard', [PageController::class, 'dashboard']);

Route::prefix('workshop')->name('workshop.')->group(function () {
    Route::get('/dashboard', [WorkshopController::class, 'dashboard'])->name('dashboard');
    Route::get('/job-cards', [WorkshopController::class, 'index'])->name('job-cards.index');
    Route::post('/job-cards', [WorkshopController::class, 'storeJobCard'])->name('job-cards.store');
    Route::get('/job-cards/{jobCard}', [WorkshopController::class, 'show'])->name('job-cards.show');
    Route::post('/job-cards/{jobCard}/repair-orders', [WorkshopController::class, 'storeRepairOrder'])->name('job-cards.repair-orders.store');
    Route::post('/job-cards/{jobCard}/diagnosis', [WorkshopController::class, 'storeDiagnosis'])->name('job-cards.diagnosis.store');
    Route::post('/job-cards/{jobCard}/mechanics', [WorkshopController::class, 'storeMechanic'])->name('job-cards.mechanics.store');
    Route::post('/job-cards/{jobCard}/labour', [WorkshopController::class, 'storeLabour'])->name('job-cards.labour.store');
    Route::post('/job-cards/{jobCard}/parts', [WorkshopController::class, 'storePart'])->name('job-cards.parts.store');
    Route::post('/job-cards/{jobCard}/complete', [WorkshopController::class, 'complete'])->name('job-cards.complete');
    Route::post('/job-cards/{jobCard}/inspect', [WorkshopController::class, 'inspect'])->name('job-cards.inspect');
    Route::post('/job-cards/{jobCard}/invoice', [WorkshopController::class, 'generateInvoice'])->name('job-cards.invoice');
    Route::post('/job-cards/{jobCard}/close', [WorkshopController::class, 'close'])->name('job-cards.close');
});

Route::get('/users', [UserController::class, 'index'])->name('users.list');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/types', [UserTypeController::class, 'index'])->name('users.types');
Route::post('/users/types', [UserTypeController::class, 'store'])->name('users.types.store');
Route::get('/users/view', [PageController::class, 'userView']);
// Route::get('/users/edit', [PageController::class, 'userEdit']);

//changes here 
Route::get('/users/view', [PageController::class, 'userView']);
Route::get('/users/profile', [PageController::class, 'userProfile']);

//end of changes 
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

/* Start of Syliverius */

Route::prefix('settings/other_settings/branch-departments')->name('settings.other_settings.branch_departments.')->group(function () {
    Route::get('/list', [BranchDepartmentController::class, 'list'])->name('list');
    Route::post('/', [BranchDepartmentController::class, 'store'])->name('store');
    Route::put('/{department}', [BranchDepartmentController::class, 'update'])->name('update');
    Route::delete('/{department}', [BranchDepartmentController::class, 'destroy'])->name('destroy');
});

Route::prefix('settings/other_settings/subdepartments')->name('settings.other_settings.subdepartments.')->group(function () {
    Route::get('/list', [SubdepartmentController::class, 'list'])->name('list');
    Route::post('/', [SubdepartmentController::class, 'store'])->name('store');
    Route::put('/{subdepartment}', [SubdepartmentController::class, 'update'])->name('update');
    Route::delete('/{subdepartment}', [SubdepartmentController::class, 'destroy'])->name('destroy');
});

Route::prefix('settings/other_settings/suppliers')->name('settings.other_settings.suppliers.')->group(function () {
    Route::get('/list', [SupplierController::class, 'list'])->name('list');
    Route::post('/', [SupplierController::class, 'store'])->name('store');
    Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
    Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
});

Route::prefix('settings/other_settings')->name('settings.other_settings.')->group(function () {
    Route::get('/', [Other_SettingsController::class, 'index'])->name('index');
    Route::get('/{key}/list', [Other_SettingsController::class, 'list'])->name('list');
    Route::post('/{key}', [Other_SettingsController::class, 'store'])->name('store');
    Route::put('/{key}/{lookup}', [Other_SettingsController::class, 'update'])->name('update');
    Route::delete('/{key}/{lookup}', [Other_SettingsController::class, 'destroy'])->name('destroy');
});

Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/items')->name('storage_supplies.items.')->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('index');
    Route::get('/list', [ItemController::class, 'list'])->name('list');
    Route::get('/categories', [ItemController::class, 'categories'])->name('categories');
    Route::post('/', [ItemController::class, 'store'])->name('store');
    Route::put('/{item}', [ItemController::class, 'update'])->name('update');
    Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');
});

// Route::get('/users/{user}/subdepartments', [UserSubdepartmentController::class, 'edit'])->name('users.subdepartments.edit');
// Route::post('/users/{user}/subdepartments', [UserSubdepartmentController::class, 'update'])->name('users.subdepartments.update');

//Storage and supplies 
Route::get('/storage-supplies/select-subdepartment/{module}', [SubdepartmentSessionController::class, 'index'])->name('storage-supplies.select-subdepartment');
Route::post('/storage-supplies/select-subdepartment/{module}', [SubdepartmentSessionController::class, 'store'])->name('storage-supplies.select-subdepartment.store');

Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/store-ordering')->name('storage_supplies.store_ordering.')->group(function () {
    Route::get('/new', [StoreOrderController::class, 'newOrder'])->name('new_order');
    Route::get('/items-picker', [StoreOrderController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [StoreOrderController::class, 'store'])->name('store');
    Route::get('/pending', [StoreOrderController::class, 'pendingOrder'])->name('pending_order');
    Route::post('/{storeRequisition}/approve', [StoreOrderController::class, 'approve'])->name('approve');
    Route::get('/previous', [StoreOrderController::class, 'previousOrder'])->name('previous_order');
    Route::get('/{storeRequisition}/preview', [StoreOrderController::class, 'preview'])->name('preview');
    Route::get('/{storeRequisition}/edit', [StoreOrderController::class, 'editItems'])->name('edit');
    Route::post('/{storeRequisition}/items', [StoreOrderController::class, 'updateItems'])->name('update_items');
});

//GRN Against purchase order
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/grn')->name('storage_supplies.grn.')->group(function () {
    Route::get('/new', [GrnController::class, 'newGrnList'])->name('new');
    Route::get('/new/{localPurchaseOrder}/create', [GrnController::class, 'create'])->name('create');
    Route::post('/new/{localPurchaseOrder}', [GrnController::class, 'store'])->name('store');
    Route::post('/{grn}/submit', [GrnController::class, 'submitForInspection'])->name('submit');
    Route::get('/approve', [GrnController::class, 'approveGrnList'])->name('approve');
    Route::post('/{grn}/approve', [GrnController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [GrnController::class, 'previousGrnList'])->name('previous');
    Route::get('/{grn}/preview', [GrnController::class, 'preview'])->name('preview');
});

//Procurement
Route::middleware('active.subdepartment:procurement')->prefix('procurement')->name('procurement.')->group(function () {
    Route::prefix('store-requisitions')->name('store_requisitions.')->group(function () {
        Route::get('/', [ProcurementController::class, 'storeRequisitions'])->name('index');
        Route::get('/{storeRequisition}/create-purchase-order', [ProcurementController::class, 'createPurchaseOrder'])->name('create_po');
        Route::post('/{storeRequisition}/create-purchase-order', [ProcurementController::class, 'storePurchaseOrder'])->name('store_po');
        Route::post('/{storeRequisition}/reject', [ProcurementController::class, 'rejectRequisition'])->name('reject');
        Route::get('/{storeRequisition}/preview', [ProcurementController::class, 'previewForSupplier'])->name('preview');
    });

    Route::prefix('purchase-requisition')->name('purchase_requisition.')->group(function () {
        Route::get('/', [ProcurementController::class, 'purchaseRequisitionList'])->name('index');
        Route::get('/{localPurchaseOrder}/edit', [ProcurementController::class, 'editDraft'])->name('edit');
        Route::post('/{localPurchaseOrder}', [ProcurementController::class, 'updateDraft'])->name('update');
        Route::post('/{localPurchaseOrder}/submit', [ProcurementController::class, 'submitForApproval'])->name('submit');
    });

    Route::prefix('approve-lpo')->name('approve_lpo.')->group(function () {
        Route::get('/', [ProcurementController::class, 'approveLpoList'])->name('index');
        Route::post('/{localPurchaseOrder}/approve', [ProcurementController::class, 'approveLpo'])->name('approve');
    });

    Route::prefix('local-purchase-order')->name('local_purchase_order.')->group(function () {
        Route::get('/', [ProcurementController::class, 'finalList'])->name('index');
        Route::get('/{localPurchaseOrder}/print', [ProcurementController::class, 'printLpo'])->name('print');
    });
});

//user management 
Route::prefix('users/{user}')->name('users.')->whereNumber('user')->group(function () {
    Route::get('/{tab?}', [UserWorkspaceController::class, 'show'])->name('show')->where('tab', '[a-z-]+');
    Route::get('/tab/{tab}/content', [UserWorkspaceController::class, 'tabContent'])->name('tab_content')->where('tab', '[a-z-]+');
    Route::put('/', [UserWorkspaceController::class, 'updateEmployee'])->name('update');

    Route::post('/branches', [UserWorkspaceController::class, 'addBranch'])->name('branches.add');
    Route::delete('/branches/{branch}', [UserWorkspaceController::class, 'removeBranch'])->name('branches.remove');

    //Route::get('/subdepartments/options', [UserWorkspaceController::class, 'subdepartmentOptions'])->name('subdepartments.options');
    Route::post('/subdepartments', [UserWorkspaceController::class, 'addSubdepartment'])->name('subdepartments.add');
    Route::delete('/subdepartments/{subdepartment}', [UserWorkspaceController::class, 'removeSubdepartment'])->name('subdepartments.remove');

    Route::post('/approval-permissions', [UserWorkspaceController::class, 'updateApprovalPermissions'])->name('approval_permissions.update');
    Route::post('/system-permissions', [UserWorkspaceController::class, 'updateMenuPermissions'])->name('system_permissions.update');
});

/* end of syliverius */

Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.list');
Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.list');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');

Route::get('/Logout', [PageController::class, 'logout']);
Route::get('/Logout2', [PageController::class, 'logoutRedirect']);
Route::get('/load_privacy', [PageController::class, 'loadPrivacy']);
Route::get('/profile_edit/{user}', [PageController::class, 'profileEdit']);

Route::post('/system/releases/seen', [PageController::class, 'releasesSeen']);
Route::post('/system/releases/new', [PageController::class, 'releasesNew']);
Route::post('/view_school_calendar', [PageController::class, 'viewSchoolCalendar']);
Route::post('/dashboard_school_calendar_widget', [PageController::class, 'dashboardSchoolCalendarWidget']);
Route::get('/company/load-content-view/{year}', [PageController::class, 'companyContentView']);

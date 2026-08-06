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
use App\Http\Controllers\StorageSupplies\GrnWithoutPoController;
use App\Http\Controllers\StorageSupplies\GrnOpenBalanceController;
use App\Http\Controllers\StorageSupplies\RequisitionController;
use App\Http\Controllers\StorageSupplies\IssueNoteController;
use App\Http\Controllers\StorageSupplies\GrnAgainstIssueNoteController;
use App\Http\Controllers\StorageSupplies\ReturnController;
use App\Http\Controllers\StorageSupplies\StoreTransferController;
use App\Http\Controllers\StorageSupplies\ReturnOutwardController;
use App\Http\Controllers\StorageSupplies\StockAdjustmentController;

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

//GRN without purchasing order

Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/grn-without-po')->name('storage_supplies.grn_without_po.')->group(function () {
    Route::get('/new', [GrnWithoutPoController::class, 'create'])->name('new');
    Route::get('/items-picker', [GrnWithoutPoController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [GrnWithoutPoController::class, 'store'])->name('store');
    Route::get('/pending', [GrnWithoutPoController::class, 'pendingList'])->name('pending');
    Route::get('/{grn}/edit', [GrnWithoutPoController::class, 'edit'])->name('edit');
    Route::post('/{grn}', [GrnWithoutPoController::class, 'update'])->name('update');
    Route::post('/{grn}/approve', [GrnWithoutPoController::class, 'approve'])->name('approve');
    Route::get('/previous', [GrnWithoutPoController::class, 'previousList'])->name('previous');
    Route::get('/{grn}/preview', [GrnWithoutPoController::class, 'preview'])->name('preview');
});

//GRN as open balance 
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/grn-open-balance')->name('storage_supplies.grn_open_balance.')->group(function () {
    Route::get('/new', [GrnOpenBalanceController::class, 'newList'])->name('new');
    Route::get('/create', [GrnOpenBalanceController::class, 'create'])->name('create');
    Route::get('/items-picker', [GrnOpenBalanceController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [GrnOpenBalanceController::class, 'store'])->name('store');
    Route::get('/{grn}/edit', [GrnOpenBalanceController::class, 'edit'])->name('edit');
    Route::post('/{grn}', [GrnOpenBalanceController::class, 'update'])->name('update');
    Route::post('/{grn}/submit', [GrnOpenBalanceController::class, 'submit'])->name('submit');
    Route::get('/approve', [GrnOpenBalanceController::class, 'approveList'])->name('approve');
    Route::post('/{grn}/approve', [GrnOpenBalanceController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [GrnOpenBalanceController::class, 'previousList'])->name('previous');
    Route::get('/{grn}/preview', [GrnOpenBalanceController::class, 'preview'])->name('preview');
});

//Requisition 
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/requisition')->name('storage_supplies.requisition.')->group(function () {
    Route::get('/pending', [RequisitionController::class, 'pendingList'])->name('pending');
    Route::get('/create', [RequisitionController::class, 'create'])->name('create');
    Route::get('/items-picker', [RequisitionController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [RequisitionController::class, 'store'])->name('store');
    Route::get('/{requisition}/edit', [RequisitionController::class, 'edit'])->name('edit');
    Route::post('/{requisition}', [RequisitionController::class, 'update'])->name('update');
    Route::post('/{requisition}/submit', [RequisitionController::class, 'submit'])->name('submit');
    Route::get('/approve', [RequisitionController::class, 'approveList'])->name('approve');
    Route::post('/{requisition}/approve', [RequisitionController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [RequisitionController::class, 'previousList'])->name('previous');
    Route::get('/{requisition}/preview', [RequisitionController::class, 'preview'])->name('preview');
});

//issue note
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/issue-note')->name('storage_supplies.issue_note.')->group(function () {
    Route::get('/new', [IssueNoteController::class, 'newList'])->name('new');
    Route::get('/{requisition}/create', [IssueNoteController::class, 'create'])->name('create');
    Route::post('/{requisition}', [IssueNoteController::class, 'store'])->name('store');
    Route::get('/approve', [IssueNoteController::class, 'approveList'])->name('approve');
    Route::post('/{issueNote}/approve', [IssueNoteController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [IssueNoteController::class, 'previousList'])->name('previous');
    Route::get('/{issueNote}/preview', [IssueNoteController::class, 'preview'])->name('preview');
});

//receieve issue not
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/grn-against-issue-note')->name('storage_supplies.grn_against_issue_note.')->group(function () {
    Route::get('/new', [GrnAgainstIssueNoteController::class, 'newList'])->name('new');
    Route::get('/{issueNote}/create', [GrnAgainstIssueNoteController::class, 'create'])->name('create');
    Route::post('/{issueNote}', [GrnAgainstIssueNoteController::class, 'store'])->name('store');
    Route::get('/approve', [GrnAgainstIssueNoteController::class, 'approveList'])->name('approve');
    Route::post('/{grn}/approve', [GrnAgainstIssueNoteController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [GrnAgainstIssueNoteController::class, 'previousList'])->name('previous');
    Route::get('/{grn}/preview', [GrnAgainstIssueNoteController::class, 'preview'])->name('preview');
});

//return inwards
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/return')->name('storage_supplies.return.')->group(function () {
    Route::get('/new', [ReturnController::class, 'draftList'])->name('new');
    Route::get('/create', [ReturnController::class, 'create'])->name('create');
    Route::get('/items-picker', [ReturnController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [ReturnController::class, 'store'])->name('store');
    Route::get('/{return}/edit', [ReturnController::class, 'edit'])->name('edit');
    Route::post('/{return}', [ReturnController::class, 'update'])->name('update');
    Route::post('/{return}/submit', [ReturnController::class, 'submit'])->name('submit');
    Route::get('/approve', [ReturnController::class, 'approveList'])->name('approve');
    Route::post('/{return}/approve', [ReturnController::class, 'approve'])->name('approve_submit');
    Route::get('/list', [ReturnController::class, 'returnList'])->name('return_list');
    Route::post('/{return}/receive', [ReturnController::class, 'receive'])->name('receive');
    Route::get('/previous', [ReturnController::class, 'previousList'])->name('previous');
    Route::get('/{return}/preview', [ReturnController::class, 'preview'])->name('preview');
});

//Stock transfer
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/store-transfer')->name('storage_supplies.store_transfer.')->group(function () {
    Route::get('/draft', [StoreTransferController::class, 'draftList'])->name('draft');
    Route::get('/create', [StoreTransferController::class, 'create'])->name('create');
    Route::get('/items-picker', [StoreTransferController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [StoreTransferController::class, 'store'])->name('store');
    Route::get('/{transfer}/edit', [StoreTransferController::class, 'edit'])->name('edit');
    Route::post('/{transfer}', [StoreTransferController::class, 'update'])->name('update');
    Route::post('/{transfer}/submit', [StoreTransferController::class, 'submit'])->name('submit');
    Route::post('/{transfer}/cancel', [StoreTransferController::class, 'cancel'])->name('cancel');
    Route::get('/approve', [StoreTransferController::class, 'approveList'])->name('approve');
    Route::post('/{transfer}/approve', [StoreTransferController::class, 'approve'])->name('approve_submit');
    Route::get('/pending-receipt', [StoreTransferController::class, 'pendingReceiptList'])->name('pending_receipt');
    Route::post('/{transfer}/receive', [StoreTransferController::class, 'receive'])->name('receive');
    Route::get('/completed', [StoreTransferController::class, 'completedList'])->name('completed');
    Route::get('/cancelled', [StoreTransferController::class, 'cancelledList'])->name('cancelled');
    Route::get('/{transfer}/preview', [StoreTransferController::class, 'preview'])->name('preview');
});

//Return Outward
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/return-outward')->name('storage_supplies.return_outward.')->group(function () {
    Route::get('/new', [ReturnOutwardController::class, 'draftList'])->name('new');
    Route::get('/create', [ReturnOutwardController::class, 'create'])->name('create');
    Route::get('/items-picker', [ReturnOutwardController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [ReturnOutwardController::class, 'store'])->name('store');
    Route::get('/{return}/edit', [ReturnOutwardController::class, 'edit'])->name('edit');
    Route::post('/{return}', [ReturnOutwardController::class, 'update'])->name('update');
    Route::post('/{return}/submit', [ReturnOutwardController::class, 'submit'])->name('submit');
    Route::get('/approve', [ReturnOutwardController::class, 'approveList'])->name('approve');
    Route::post('/{return}/approve', [ReturnOutwardController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [ReturnOutwardController::class, 'previousList'])->name('previous');
    Route::get('/{return}/preview', [ReturnOutwardController::class, 'preview'])->name('preview');
});

//Stock adjustment 
Route::middleware('active.subdepartment:storage-supplies')->prefix('storage-supplies/stock-adjustment')->name('storage_supplies.stock_adjustment.')->group(function () {
    Route::get('/new', [StockAdjustmentController::class, 'draftList'])->name('new');
    Route::get('/create', [StockAdjustmentController::class, 'create'])->name('create');
    Route::get('/items-picker', [StockAdjustmentController::class, 'itemsPicker'])->name('items_picker');
    Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
    Route::get('/{adjustment}/edit', [StockAdjustmentController::class, 'edit'])->name('edit');
    Route::post('/{adjustment}', [StockAdjustmentController::class, 'update'])->name('update');
    Route::post('/{adjustment}/submit', [StockAdjustmentController::class, 'submit'])->name('submit');
    Route::get('/approve', [StockAdjustmentController::class, 'approveList'])->name('approve');
    Route::post('/{adjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('approve_submit');
    Route::get('/previous', [StockAdjustmentController::class, 'previousList'])->name('previous');
    Route::get('/{adjustment}/preview', [StockAdjustmentController::class, 'preview'])->name('preview');
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

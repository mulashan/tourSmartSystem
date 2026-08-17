<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SystemDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompany();
        $branchId = $this->seedBranch();
        $roles = $this->seedUserTypes();
        $menus = $this->seedMenus();
        $this->seedAdminPermissions($roles['admin'], $menus);
        $this->seedAdminUser($branchId, $roles['admin']);
    }

    private function seedCompany(): void
    {
        DB::table('tbl_company')->updateOrInsert(
            ['Company_Name' => 'Leopard Tours'],
            ['status' => 'active']
        );
    }

    private function seedBranch(): int
    {
        $companyId = DB::table('tbl_company')->where('Company_Name', 'Leopard Tours')->value('Company_ID');

        $data = [
            'Branch_Name' => 'Head Office',
            'token' => 'HQ-DEFAULT',
            'token_date' => now(),
            'BannerLink' => 'https://example.com/banner.jpg',
            'Company_ID' => $companyId,
        ];

        if (Schema::hasColumn('tbl_branches', 'Location')) {
            $data['Location'] = 'Dar es Salaam';
        }

        if (Schema::hasColumn('tbl_branches', 'Manager')) {
            $data['Manager'] = 'System Admin';
        }

        if (Schema::hasColumn('tbl_branches', 'status')) {
            $data['status'] = 'Active';
        }

        DB::table('tbl_branches')->updateOrInsert(
            ['Branch_Name' => 'Head Office'],
            $data
        );

        return (int) DB::table('tbl_branches')->where('Branch_Name', 'Head Office')->value('Branch_ID');
    }

    private function seedUserTypes(): array
    {
        $types = [
            'admin' => [
                'privilege_name' => 'Admin',
                'privilege_desc' => 'Full system access and permission assignment.',
                'access_level_id' => 9,
                'priv_status' => true,
            ],
            'manager' => [
                'privilege_name' => 'Manager',
                'privilege_desc' => 'Operational access for branch and team management.',
                'access_level_id' => 5,
                'priv_status' => true,
            ],
            'user' => [
                'privilege_name' => 'User',
                'privilege_desc' => 'Standard user access.',
                'access_level_id' => 1,
                'priv_status' => true,
            ],
        ];

        $ids = [];

        foreach ($types as $key => $type) {
            DB::table('tbl_users_privileges')->updateOrInsert(
                ['privilege_name' => $type['privilege_name']],
                $type
            );

            $ids[$key] = (int) DB::table('tbl_users_privileges')
                ->where('privilege_name', $type['privilege_name'])
                ->value('id');
        }

        return $ids;
    }

    private function seedMenus(): array
    {
        $menus = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid', 'url' => 'dashboard', 'parent' => null, 'dashboard' => 1],
            ['key' => 'dashboards', 'label' => 'Dashboards', 'icon' => 'bi-speedometer2', 'url' => null, 'parent' => null],
            ['key' => 'users.setup', 'label' => 'Users setup', 'icon' => 'bi-people', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'users.list', 'label' => 'Users List', 'icon' => null, 'url' => 'users', 'parent' => 'users.setup'],
            ['key' => 'users.types', 'label' => 'User Types', 'icon' => null, 'url' => 'users/types', 'parent' => 'users.setup'],
            ['key' => 'users.view', 'label' => 'User View', 'icon' => null, 'url' => 'users/view', 'parent' => 'users.setup'],
            //['key' => 'users.edit', 'label' => 'User Edit', 'icon' => null, 'url' => 'users/edit', 'parent' => 'users.setup'],
            ['key' => 'users.profile', 'label' => 'Profile', 'icon' => null, 'url' => 'users/profile', 'parent' => 'users.setup'],

            ['key' => 'users.settings.group', 'label' => 'Settings', 'icon' => null, 'url' => null, 'parent' => 'users.setup', 'collapse' => 1],
            ['key' => 'users.settings', 'label' => 'Account', 'icon' => null, 'url' => 'users/settings', 'parent' => 'users.setup', 'parent2' => 'users.settings.group'],
            ['key' => 'users.notifications', 'label' => 'Notifications', 'icon' => null, 'url' => 'users/notifications', 'parent' => 'users.setup', 'parent2' => 'users.settings.group'],
            ['key' => 'users.activity', 'label' => 'Activity', 'icon' => null, 'url' => 'users/activity', 'parent' => 'users.setup', 'parent2' => 'users.settings.group'],
            ['key' => 'users.roles', 'label' => 'Roles & Permissions', 'icon' => null, 'url' => 'users/roles-permissions', 'parent' => 'users.setup'],

            ['key' => 'settings.setup', 'label' => 'Setting', 'icon' => 'bi-gear', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'settings.branch', 'label' => 'Branch', 'icon' => null, 'url' => 'settings/branch', 'parent' => 'settings.setup'],
            ['key' => 'settings.employee-job-codes', 'label' => 'Employee Job Codes', 'icon' => null, 'url' => 'settings/employee-job-codes', 'parent' => 'settings.setup'],
            ['key' => 'settings.hr-employment-types', 'label' => 'HR Employment Type', 'icon' => null, 'url' => 'settings/hr-employment-types', 'parent' => 'settings.setup'],
            ['key' => 'settings.job-titles', 'label' => 'Job Titles', 'icon' => null, 'url' => 'settings/job-titles', 'parent' => 'settings.setup'],
            ['key' => 'settings.department-natures', 'label' => 'Department Nature', 'icon' => null, 'url' => 'settings/department-natures', 'parent' => 'settings.setup'],
            ['key' => 'settings.designations', 'label' => 'Designation', 'icon' => null, 'url' => 'settings/designations', 'parent' => 'settings.setup'],
            ['key' => 'settings.employee-units', 'label' => 'Employee Units', 'icon' => null, 'url' => 'settings/employee-units', 'parent' => 'settings.setup'],
            ['key' => 'departments.list', 'label' => 'Department', 'icon' => 'bi-diagram-3', 'url' => 'departments', 'parent' => null],
            ['key' => 'employees.list', 'label' => 'Employee', 'icon' => 'bi-person-badge', 'url' => 'employees', 'parent' => null],

            //storage and supplies
            ['key' => 'storage-supplies.setup', 'label' => 'Storage and Supplies', 'icon' => 'bi bi-cart-check-fill', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'storage-supplies.items', 'label' => 'Item Manager', 'icon' => null, 'url' => 'storage-supplies/items', 'parent' => 'storage-supplies.setup'],
            ['key' => 'storage-supplies.ordering.group', 'label' => 'Store Ordering', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.ordering.new', 'label' => 'New Order', 'icon' => null, 'url' => 'storage-supplies/store-ordering/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.ordering.group'],
            ['key' => 'storage-supplies.ordering.pending', 'label' => 'Pending Orders', 'icon' => null, 'url' => 'storage-supplies/store-ordering/pending', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.ordering.group'],
            ['key' => 'storage-supplies.ordering.previous', 'label' => 'Previous Orders', 'icon' => null, 'url' => 'storage-supplies/store-ordering/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.ordering.group'],
            //GRN Against Purchasing order
            ['key' => 'storage-supplies.grn-against-order', 'label' => 'GRN Agaist Purchase Order', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.grn-against-order.new', 'label' => 'New GRN', 'icon' => null, 'url' => 'storage-supplies/grn/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-against-order'],
            ['key' => 'storage-supplies.grn-against-order.approve', 'label' => 'Approve GRN', 'icon' => null, 'url' => 'storage-supplies/grn/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-against-order'],
            ['key' => 'storage-supplies.grn.previous', 'label' => 'Previous GRN List', 'icon' => null, 'url' => 'storage-supplies/grn/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-against-order'],

            //GRN Without Purchasing Order
            ['key' => 'storage-supplies.grn-without-order', 'label' => 'GRN Without Purchase Order', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.grn-without-order.new', 'label' => 'New GRN', 'icon' => null, 'url' => 'storage-supplies/grn-without-po/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-without-order'],
            ['key' => 'storage-supplies.grn-without-order.approve', 'label' => 'Approve GRN', 'icon' => null, 'url' => 'storage-supplies/grn-without-po/pending', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-without-order'],
            ['key' => 'storage-supplies.grn-without-order.previous', 'label' => 'Previous GRN List', 'icon' => null, 'url' => 'storage-supplies/grn-without-po/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-without-order'],

            //GRN as Open balance 
            ['key' => 'storage-supplies.grn-open-balance.group', 'label' => 'GRN Open Balance / Physical Count', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.grn-open-balance.new', 'label' => 'New GRN', 'icon' => null, 'url' => 'storage-supplies/grn-open-balance/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-open-balance.group'],
            ['key' => 'storage-supplies.grn-open-balance.approve', 'label' => 'Approve GRN', 'icon' => null, 'url' => 'storage-supplies/grn-open-balance/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-open-balance.group'],
            ['key' => 'storage-supplies.grn-open-balance.previous', 'label' => 'Previous GRN List', 'icon' => null, 'url' => 'storage-supplies/grn-open-balance/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-open-balance.group'],

            //GRN ISSUE
            ['key' => 'storage-supplies.grn-issue.group', 'label' => 'GRN Against Issue Note', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.grn-issue.new', 'label' => 'New GRN', 'icon' => null, 'url' => 'storage-supplies/grn-against-issue-note/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-issue.group'],
            ['key' => 'storage-supplies.grn-issue.approve', 'label' => 'Approve GRN', 'icon' => null, 'url' => 'storage-supplies/grn-against-issue-note/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-issue.group'],
            ['key' => 'storage-supplies.grn-issue.previous', 'label' => 'Previous GRN List', 'icon' => null, 'url' => 'storage-supplies/grn-against-issue-note/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.grn-issue.group'],

            //Store requisitions
            ['key' => 'storage-supplies.requisition.group', 'label' => 'Requisition', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.requisition.new', 'label' => 'New Requisition', 'icon' => null, 'url' => 'storage-supplies/requisition/create', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.requisition.group'],
            ['key' => 'storage-supplies.requisition.pending', 'label' => 'Pending Requisition', 'icon' => null, 'url' => 'storage-supplies/requisition/pending', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.requisition.group'],
            ['key' => 'storage-supplies.requisition.approve', 'label' => 'Approve Requisition', 'icon' => null, 'url' => 'storage-supplies/requisition/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.requisition.group'],
            ['key' => 'storage-supplies.requisition.previous', 'label' => 'Previous Requisition', 'icon' => null, 'url' => 'storage-supplies/requisition/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.requisition.group'],

            //Issue Note Electronic
            ['key' => 'storage-supplies.issue-note.group', 'label' => 'Issue Note - Electronic', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.issue-note.new', 'label' => 'New Issue Note', 'icon' => null, 'url' => 'storage-supplies/issue-note/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.issue-note.group'],
            ['key' => 'storage-supplies.issue-note.approve', 'label' => 'Approve Issues', 'icon' => null, 'url' => 'storage-supplies/issue-note/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.issue-note.group'],
            ['key' => 'storage-supplies.issue-note.previous', 'label' => 'Previous Issues', 'icon' => null, 'url' => 'storage-supplies/issue-note/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.issue-note.group'],

            //Return inward
            ['key' => 'storage-supplies.return-inward.group', 'label' => 'Return Inward', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.return-inward.new', 'label' => 'New Return', 'icon' => null, 'url' => 'storage-supplies/return/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-inward.group'],
            ['key' => 'storage-supplies.return-inward.approve', 'label' => 'Approve Return', 'icon' => null, 'url' => 'storage-supplies/return/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-inward.group'],
            ['key' => 'storage-supplies.return.return-list', 'label' => 'Return List', 'icon' => null, 'url' => 'storage-supplies/return/list', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-inward.group'],
            ['key' => 'storage-supplies.return-inward.previous', 'label' => 'Previous Return', 'icon' => null, 'url' => 'storage-supplies/return/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-inward.group'],

            //Transfer
            ['key' => 'storage-supplies.store-transfer.group', 'label' => 'Store Transfer', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.store-transfer.draft', 'label' => 'Draft (New Transfer)', 'icon' => null, 'url' => 'storage-supplies/store-transfer/draft', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.store-transfer.group'],
            ['key' => 'storage-supplies.store-transfer.approve', 'label' => 'Pending Approval', 'icon' => null, 'url' => 'storage-supplies/store-transfer/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.store-transfer.group'],
            ['key' => 'storage-supplies.store-transfer.pending-receipt', 'label' => 'Pending Receipt', 'icon' => null, 'url' => 'storage-supplies/store-transfer/pending-receipt', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.store-transfer.group'],
            ['key' => 'storage-supplies.store-transfer.completed', 'label' => 'Completed Transfers', 'icon' => null, 'url' => 'storage-supplies/store-transfer/completed', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.store-transfer.group'],
            ['key' => 'storage-supplies.store-transfer.cancelled', 'label' => 'Cancelled Transfers', 'icon' => null, 'url' => 'storage-supplies/store-transfer/cancelled', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.store-transfer.group'],

            //Return outward to supplier
            ['key' => 'storage-supplies.return-outward.group', 'label' => 'Return Outward', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.return-outward.new', 'label' => 'New Return', 'icon' => null, 'url' => 'storage-supplies/return-outward/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-outward.group'],
            ['key' => 'storage-supplies.return-outward.approve', 'label' => 'Approve Return', 'icon' => null, 'url' => 'storage-supplies/return-outward/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-outward.group'],
            ['key' => 'storage-supplies.return-outward.previous', 'label' => 'Previous Return', 'icon' => null, 'url' => 'storage-supplies/return-outward/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.return-outward.group'],

            //Stock Adjustment
            ['key' => 'storage-supplies.stock-adjustment.group', 'label' => 'Adjustment', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.stock-adjustment.new', 'label' => 'New Adjustment', 'icon' => null, 'url' => 'storage-supplies/stock-adjustment/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.stock-adjustment.group'],
            ['key' => 'storage-supplies.stock-adjustment.approve', 'label' => 'Approve Adjustments', 'icon' => null, 'url' => 'storage-supplies/stock-adjustment/approve', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.stock-adjustment.group'],
            ['key' => 'storage-supplies.stock-adjustment.previous', 'label' => 'Previous Adjustments', 'icon' => null, 'url' => 'storage-supplies/stock-adjustment/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.stock-adjustment.group'],

            ['key' => 'storage-supplies.service-use.group', 'label' => 'Service Use', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.service-use.new', 'label' => 'New Service Use', 'icon' => null, 'url' => 'storage-supplies/service-use/new', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.service-use.group'],
            ['key' => 'storage-supplies.service-use.previous', 'label' => 'Previous Service Use', 'icon' => null, 'url' => 'storage-supplies/service-use/previous', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.service-use.group'],


            //Storage and Supplies Reports
            ['key' => 'storage-supplies.reports.group', 'label' => 'Reports', 'icon' => null, 'url' => null, 'parent' => 'storage-supplies.setup', 'collapse' => 1],
            ['key' => 'storage-supplies.reports.stock-summary', 'label' => 'Stock Summary', 'icon' => null, 'url' => 'storage-supplies/reports/stock-summary', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.stock-ledger', 'label' => 'Stock Ledger', 'icon' => null, 'url' => 'storage-supplies/reports/stock-ledger', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.expiring-items', 'label' => 'Expiring Items', 'icon' => null, 'url' => 'storage-supplies/reports/expiring-items', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.purchase', 'label' => 'Purchase Report', 'icon' => null, 'url' => 'storage-supplies/reports/purchase-report', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.grn', 'label' => 'GRN Report', 'icon' => null, 'url' => 'storage-supplies/reports/grn-report', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.batch-management', 'label' => 'Batch Management', 'icon' => null, 'url' => 'storage-supplies/reports/batch-management', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.store-issuing', 'label' => 'Store Issuing Report', 'icon' => null, 'url' => 'storage-supplies/reports/store-issuing', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.quantity-issuing', 'label' => 'Quantity Issuing Report', 'icon' => null, 'url' => 'storage-supplies/reports/quantity-issuing', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.store-balance', 'label' => 'Store Balance Report', 'icon' => null, 'url' => 'storage-supplies/reports/store-balance', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.dormant-items', 'label' => 'Dormant / Slow-Moving Items', 'icon' => null, 'url' => 'storage-supplies/reports/dormant-items', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.requisition-fulfillment', 'label' => 'Requisition Fulfillment', 'icon' => null, 'url' => 'storage-supplies/reports/requisition-fulfillment', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.approval-turnaround', 'label' => 'Approval Turnaround', 'icon' => null, 'url' => 'storage-supplies/reports/approval-turnaround', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.consumption-trend', 'label' => 'Consumption Trend', 'icon' => null, 'url' => 'storage-supplies/reports/consumption-trend', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],
            ['key' => 'storage-supplies.reports.wastage-loss', 'label' => 'Wastage / Loss', 'icon' => null, 'url' => 'storage-supplies/reports/wastage-loss', 'parent' => 'storage-supplies.setup', 'parent2' => 'storage-supplies.reports.group'],


            //procuremet
            ['key' => 'Procurement.setup', 'label' => 'Procurements', 'icon' => 'bi-cart-check', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'Procurement.store-order-requisition', 'label' => 'Store Order Requisitions', 'icon' => null, 'url' => 'procurement/store-requisitions', 'parent' => 'Procurement.setup'],
            ['key' => 'procurement.purchase-requisition', 'label' => 'Purchase Requisition', 'icon' => null, 'url' => 'procurement/purchase-requisition', 'parent' => 'Procurement.setup'],
            ['key' => 'procurement.approve-lpo', 'label' => 'Approve Local Purchase Order', 'icon' => null, 'url' => 'procurement/approve-lpo', 'parent' => 'Procurement.setup'],
            ['key' => 'procurement.local-purchase-order', 'label' => 'Local Purchase Order', 'icon' => null, 'url' => 'procurement/local-purchase-order', 'parent' => 'Procurement.setup'],

            //procurement reports
            ['key' => 'procurement.reports.group', 'label' => 'Reports', 'icon' => null, 'url' => null, 'parent' => 'Procurement.setup', 'collapse' => 1],
            ['key' => 'procurement.reports.purchasing-history', 'label' => 'Purchasing History', 'icon' => null, 'url' => 'procurement/reports/purchasing-history', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.previous-purchase-requisition', 'label' => 'Previous Purchase Requisition', 'icon' => null, 'url' => 'procurement/reports/previous-purchase-requisition', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.received-grn', 'label' => 'Received GRN', 'icon' => null, 'url' => 'procurement/reports/received-grn', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.procurement-report', 'label' => 'Procurement Report', 'icon' => null, 'url' => 'procurement/reports/procurement-report', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.cancelled-purchase-requisition', 'label' => 'Cancelled Purchase Requisition', 'icon' => null, 'url' => 'procurement/reports/cancelled-purchase-requisition', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.last-buying-price', 'label' => 'Last Buying Price', 'icon' => null, 'url' => 'procurement/reports/last-buying-price', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.pending-po-aging', 'label' => 'Pending PO Aging', 'icon' => null, 'url' => 'procurement/reports/pending-po-aging', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.supplier-price-trend', 'label' => 'Supplier Price Trend', 'icon' => null, 'url' => 'procurement/reports/supplier-price-trend', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.requisition-rejection-rate', 'label' => 'Requisition Rejection Rate', 'icon' => null, 'url' => 'procurement/reports/requisition-rejection-rate', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],
            ['key' => 'procurement.reports.top-suppliers-by-spend', 'label' => 'Top Suppliers by Spend', 'icon' => null, 'url' => 'procurement/reports/top-suppliers-by-spend', 'parent' => 'Procurement.setup', 'parent2' => 'procurement.reports.group'],

            //Fleet Master
            ['key' => 'fleet.setup', 'label' => 'Fleet Master', 'icon' => 'bi-truck', 'url' => null, 'parent' => null, 'collapse' => 1],
            ['key' => 'fleet.dashboard', 'label' => 'Dashboard', 'icon' => null, 'url' => 'fleet/dashboard', 'parent' => 'fleet.setup'],
            ['key' => 'fleet.vehicles', 'label' => 'Vehicles', 'icon' => null, 'url' => 'fleet/vehicles', 'parent' => 'fleet.setup'],
            ['key' => 'fleet.insurance', 'label' => 'Vehicle Insurance', 'icon' => null, 'url' => 'fleet/insurance', 'parent' => 'fleet.setup'],
            ['key' => 'fleet.maintenance', 'label' => 'Maintenance Order', 'icon' => null, 'url' => 'fleet/maintenance', 'parent' => 'fleet.setup'],

            ['key' => 'fleet.itineraries.group', 'label' => 'Itineraries', 'icon' => null, 'url' => null, 'parent' => 'fleet.setup', 'collapse' => 1],
            ['key' => 'fleet.itineraries.new', 'label' => 'New Itinerary', 'icon' => null, 'url' => 'fleet/itineraries/new', 'parent' => 'fleet.setup', 'parent2' => 'fleet.itineraries.group'],
            ['key' => 'fleet.itineraries.approve', 'label' => 'Approve Itineraries', 'icon' => null, 'url' => 'fleet/itineraries/approve', 'parent' => 'fleet.setup', 'parent2' => 'fleet.itineraries.group'],
            ['key' => 'fleet.itineraries.assign', 'label' => 'Assign Vehicle & Driver', 'icon' => null, 'url' => 'fleet/itineraries/assign', 'parent' => 'fleet.setup', 'parent2' => 'fleet.itineraries.group'],
            ['key' => 'fleet.itineraries.active', 'label' => 'Active Trips', 'icon' => null, 'url' => 'fleet/itineraries/active', 'parent' => 'fleet.setup', 'parent2' => 'fleet.itineraries.group'],

            ['key' => 'fleet.fuel.group', 'label' => 'Fuel Assignment', 'icon' => null, 'url' => null, 'parent' => 'fleet.setup', 'collapse' => 1],
            ['key' => 'fleet.fuel.assign', 'label' => 'Assign Itinerary Fuel', 'icon' => null, 'url' => 'fleet/fuel/assign', 'parent' => 'fleet.setup', 'parent2' => 'fleet.fuel.group'],
            ['key' => 'fleet.fuel.issue', 'label' => 'Issue Itinerary Fuel', 'icon' => null, 'url' => 'fleet/fuel/issue', 'parent' => 'fleet.setup', 'parent2' => 'fleet.fuel.group'],
            ['key' => 'fleet.fuel.history', 'label' => 'Fuel History', 'icon' => null, 'url' => 'fleet/fuel/history', 'parent' => 'fleet.setup', 'parent2' => 'fleet.fuel.group'],
            ['key' => 'fleet.fuel.open-orders', 'label' => 'Emergency / Open Fuel Order', 'icon' => null, 'url' => 'fleet/fuel/open-orders', 'parent' => 'fleet.setup', 'parent2' => 'fleet.fuel.group'],
            ['key' => 'fleet.fuel.reconciliation', 'label' => 'Fuel Reconciliation', 'icon' => null, 'url' => 'fleet/fuel/reconciliation', 'parent' => 'fleet.setup', 'parent2' => 'fleet.fuel.group'],

            ['key' => 'fleet.gate-pass.group', 'label' => 'Gate Pass', 'icon' => null, 'url' => null, 'parent' => 'fleet.setup', 'collapse' => 1],
             ['key' => 'fleet.gate-pass.generate', 'label' => 'Generate Gate Pass', 'icon' => null, 'url' => 'fleet/gate-pass/generate', 'parent' => 'fleet.setup', 'parent2' => 'fleet.gate-pass.group'],
             ['key' => 'fleet.gate-pass.generatede', 'label' => 'Generated Gate Passes', 'icon' => null, 'url' => 'fleet/gate-pass/generated', 'parent' => 'fleet.setup', 'parent2' => 'fleet.gate-pass.group'],

            ['key' => 'fleet.incidents', 'label' => 'Accidents & Road Fines', 'icon' => null, 'url' => 'fleet/incidents', 'parent' => 'fleet.setup'],

            ['key' => 'fleet.reports.group', 'label' => 'Reports', 'icon' => null, 'url' => null, 'parent' => 'fleet.setup', 'collapse' => 1],
            ['key' => 'fleet.reports.vehicle-utilization', 'label' => 'Vehicle Utilization', 'icon' => null, 'url' => 'fleet/reports/vehicle-utilization', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.fuel-consumption', 'label' => 'Fuel Consumption', 'icon' => null, 'url' => 'fleet/reports/fuel-consumption', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.trip-history', 'label' => 'Trip History', 'icon' => null, 'url' => 'fleet/reports/trip-history', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.insurance-expiry', 'label' => 'Insurance Expiry', 'icon' => null, 'url' => 'fleet/reports/insurance-expiry', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.maintenance-history', 'label' => 'Maintenance History', 'icon' => null, 'url' => 'fleet/reports/maintenance-history', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.incidents', 'label' => 'Accidents & Fines', 'icon' => null, 'url' => 'fleet/reports/incidents', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.fuel-by-station', 'label' => 'Fuel by Petrol Station', 'icon' => null, 'url' => 'fleet/reports/fuel-by-station', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.cost-per-km', 'label' => 'Cost per Kilomete', 'icon' => null, 'url' => 'fleet/reports/cost-per-km', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.driver-performance', 'label' => 'Driver Performance', 'icon' => null, 'url' => 'fleet/reports/driver-performance', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.odometer-anomaly', 'label' => 'Odometer Anomaly', 'icon' => null, 'url' => 'fleet/reports/odometer-anomaly', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.maintenance-due', 'label' => 'Predictive Maintenance Due', 'icon' => null, 'url' => 'fleet/reports/maintenance-due', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.vehicle-downtime', 'label' => 'Vehicle Downtime', 'icon' => null, 'url' => 'fleet/reports/vehicle-downtime', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],
            ['key' => 'fleet.reports.destination-frequency', 'label' => 'Destination Frequency', 'icon' => null, 'url' => 'fleet/reports/destination-frequency', 'parent' => 'fleet.setup', 'parent2' => 'fleet.reports.group'],

        ];

        $ids = [];
        $nextModuleId = ((int) DB::table('tbl_menus')->max('module_id')) + 1;

        foreach ($menus as $menu) {
            $parentId = $menu['parent'] ? ($ids[$menu['parent']] ?? null) : null;
            $parentId2 = !empty($menu['parent2']) ? ($ids[$menu['parent2']] ?? null) : null;

            $data = [
                'name' => $menu['key'],
                'label' => $menu['label'],
                'menu_icon' => $menu['icon'],
                'route_path' => $menu['url'],
                'parent_id' => $parentId,
                'is_menu' => true,
                'description' => $menu['label'],
                'is_dashboard' => $menu['dashboard'] ?? 0,
                'collapse' => $menu['collapse'] ?? 0,
                'new_message' => $menu['new_message'] ?? 0,
            ];

            if (Schema::hasColumn('tbl_menus', 'parent_id2')) {
                $data['parent_id2'] = $parentId2;
            }

            $existingId = DB::table('tbl_menus')->where('name', $menu['key'])->value('module_id');

            if ($existingId) {
                DB::table('tbl_menus')->where('module_id', $existingId)->update($data);
            } else {
                DB::table('tbl_menus')->insert(array_merge(['module_id' => $nextModuleId++], $data));
            }

            $ids[$menu['key']] = (int) DB::table('tbl_menus')->where('name', $menu['key'])->value('module_id');
        }

        return $ids;
    }

    private function seedAdminPermissions(int $adminPrivilegeId, array $menus): void
    {
        foreach (array_keys($menus) as $menuKey) {
            DB::table('user_type_menu_permissions')->updateOrInsert(
                ['privilege_id' => $adminPrivilegeId, 'menu_key' => $menuKey],
                ['can_access' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function seedAdminUser(int $branchId, int $adminPrivilegeId): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'branch_id' => $branchId,
                'privilege_id' => $adminPrivilegeId,
                'photo' => null,
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function menus(string $active = 'dashboard'): array
    {
        return $this->applyNotificationBadges($this->filterMenus($this->catalog($active)));
    }

    private function notificationCounts(): array
    {
        $subdepartmentId = session('active_subdepartment_id');
        $module = session('active_subdepartment_module');

        if (! $subdepartmentId || ! $module) {
            return [];
        }

        $counts = [];

        if ($module === 'storage-supplies') {
            $counts['storage-supplies.store-ordering.pending-order'] = \App\Models\StoreRequisition::where('subdepartment_id', $subdepartmentId)
                ->where('status', 'pending')->count();

            $counts['storage-supplies.store-ordering'] = $counts['storage-supplies.store-ordering.pending-order'];
        }

        if ($module === 'procurement') {
            $counts['procurement.store-requisitions'] = \App\Models\StoreRequisition::where('status', 'approved')
                ->where('procurement_status', 'pending')
                ->whereDoesntHave('localPurchaseOrder')
                ->count();

            $counts['procurement.purchase-requisition'] = \App\Models\LocalPurchaseOrder::where('procurement_subdepartment_id', $subdepartmentId)
                ->where('status', 'draft')->count();

            $counts['procurement.approve-lpo'] = \App\Models\LocalPurchaseOrder::where('procurement_subdepartment_id', $subdepartmentId)
                ->where('status', 'pending_approval')->count();

            $counts['procurement.setup'] = $counts['procurement.store-requisitions'] + $counts['procurement.purchase-requisition'] + $counts['procurement.approve-lpo'];
        }

        return $counts;
    }

    private function applyNotificationBadges(array $menus): array
    {
        $counts = $this->notificationCounts();

        if (empty($counts)) {
            return $menus;
        }

        $walk = function (array $item) use (&$walk, $counts): array {
            if (! empty($counts[$item['key']])) {
                $item['badge'] = (string) $counts[$item['key']];
            }

            if (! empty($item['children'])) {
                $item['children'] = array_map($walk, $item['children']);
            }

            return $item;
        };

        return collect($menus)->map(function (array $group) use ($walk): array {
            $group['items'] = array_map($walk, $group['items']);

            return $group;
        })->all();
    }
}

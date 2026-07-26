<?php

namespace Database\Seeders;

use App\Models\ApprovalPermission;
use Illuminate\Database\Seeder;

class ApprovalPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'key' => 'store_ordering_approval',
                'label' => 'Store Ordering Approval',
                'description' => 'Approve store requisition orders moving from Pending to Previous Order.',
            ],
            [
                'key' => 'grn_against_order_approval',
                'label' => 'GRN Against Purchase Order Approval',
                'description' => 'Approve Goods Receiving Notes raised against an existing Purchase Order.',
            ],
            [
                'key' => 'grn_without_order_approval',
                'label' => 'GRN Without Purchase Order Approval',
                'description' => 'Approve Goods Receiving Notes with no linked Purchase Order.',
            ],
            [
                'key' => 'store_requisition_approval',
                'label' => 'Store Requisition Approval',
                'description' => 'Approve internal requisitions between store subdepartments.',
            ],
            [
                'key' => 'store_adjustment_approval',
                'label' => 'Store Adjustment Approval',
                'description' => 'Approve stock adjustment entries.',
            ],
            [
                'key' => 'purchase_order_approval',
                'label' => 'Purchase Order Approval',
                'description' => 'Approve Local Purchase Orders before they are finalized and sent to the supplier.',
            ],
        ];

        foreach ($permissions as $permission) {
            ApprovalPermission::firstOrCreate(['key' => $permission['key']], $permission);
        }
    }
}
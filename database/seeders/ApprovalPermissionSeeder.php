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
            [
                'key' => 'grn_open_balance_approval',
                'label' => 'GRN Open Balance / Physical Count Approval',
                'description' => 'Approve opening balance or physical count stock entries.',
            ],
            [
                'key' => 'issue_note_approval',
                'label' => 'Issue Note Approval',
                'description' => 'Approve electronic issue notes committing stock to be transferred.',
            ],
            [
                'key' => 'return_inward_approval', 
                'label' => 'Return Inward Approval', 
                'description' => 'Approve items returned into the store.'
            ],
            [
                'key' => 'store_transfer_approval', 
                'label' => 'Store Transfer Approval', 
                'description' => 'Approve stock transfers between stores before dispatch.'
            ],
            [
                'key' => 'return_outward_approval', 
                'label' => 'Return Outward Approval', 
                'description' => 'Approve items returned to suppliers, deducting them from store stock.'
            ],
        ];

        foreach ($permissions as $permission) {
            ApprovalPermission::firstOrCreate(['key' => $permission['key']], $permission);
        }
    }
}
<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\GrnAgainstIssueNote;
use App\Models\GrnItem;
use App\Models\GrnOpenBalance;
use App\Models\GrnPurchaseOrder;
use App\Models\GrnWithoutPo;
use App\Models\IssueNoteItem;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\LocalPurchaseOrderItem;
use App\Models\Requisition;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Subdepartment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function eligibleSubdepartments()
    {
        $user = User::with('subdepartments.department.departmentNature')->find(session('user_id'));

        return ($user?->subdepartments ?? collect())
            ->filter(fn ($sub) => optional($sub->department?->departmentNature)->department_nature === 'Storage and Supplies')
            ->sortBy('Subdepartment_Name')
            ->values();
    }

    private function allStorageSubdepartments()
    {
        return Subdepartment::whereHas('department.departmentNature', fn ($q) => $q->where('department_nature', 'Storage and Supplies'))
            ->orderBy('Subdepartment_Name')
            ->get();
    }

    private function assertEligibleSubdepartment(int $subdepartmentId): void
    {
        abort_unless($this->eligibleSubdepartments()->contains('Subdepartment_ID', $subdepartmentId), Response::HTTP_FORBIDDEN, 'You are not assigned to that Sub Department.');
    }

    private function itemCode(Item $item): string
    {
        return trim(($item->product_code_prefix ?? '') . ' ' . ($item->product_code ?? '')) ?: '—';
    }

    // ---------- 1. Stock Summary ----------

    public function stockSummary(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.stock_summary', 'storage-supplies.reports.stock-summary', [
            'subdepartments' => $this->eligibleSubdepartments(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function stockSummaryData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $itemsQuery = Item::query()->with('unitOfMeasure')
            ->when($request->query('item_category_id'), fn ($q, $c) => $q->where('item_category_id', $c))
            ->when($request->query('item_name'), fn ($q, $s) => $q->where('product_name', 'like', "%{$s}%"));

        $request->query('order_by') === 'name_desc' ? $itemsQuery->orderByDesc('product_name') : $itemsQuery->orderBy('product_name');

        $rows = $itemsQuery->get()->map(function ($item) use ($subdepartmentId, $startDate, $endDate) {
            $openRow = StockLedger::where('item_id', $item->id)->where('subdepartment_id', $subdepartmentId)
                ->where('moved_at', '<', $startDate . ' 00:00:00')
                ->orderByDesc('moved_at')->orderByDesc('id')->first();

            $ledger = StockLedger::where('item_id', $item->id)->where('subdepartment_id', $subdepartmentId)
                ->whereBetween('moved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])->get();

            $currentBalance = ItemStockBalance::where('item_id', $item->id)->where('subdepartment_id', $subdepartmentId)->value('quantity_balance') ?? 0;
            $avgPrice = StockBatch::where('item_id', $item->id)->where('subdepartment_id', $subdepartmentId)->where('quantity_remaining', '>', 0)->avg('buying_price') ?? 0;

            return [
                'item' => $item,
                'open_balance' => $openRow->balance_after ?? 0,
                'received' => $ledger->whereIn('movement_type', ['grn_receipt', 'transfer_in', 'opening_balance'])->sum('quantity_in'),
                'dispensed' => $ledger->where('movement_type', 'service_use')->sum('quantity_out'),
                'returned' => $ledger->whereIn('movement_type', ['return_inward', 'return_in'])->sum('quantity_in'),
                'issued' => $ledger->whereIn('movement_type', ['issue', 'transfer_out', 'return_outward', 'return_out'])->sum('quantity_out'),
                'adjustment_plus' => $ledger->where('movement_type', 'adjustment_add')->sum('quantity_in'),
                'adjustment_minus' => $ledger->where('movement_type', 'adjustment_deduct')->sum('quantity_out'),
                'stock_value' => $currentBalance * $avgPrice,
            ];
        });

        return view('templates.storage_supplies.reports.partials.stock_summary_table', compact('rows'));
    }

    // ---------- 2. Stock Ledger ----------

    public function stockLedger(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.stock_ledger', 'storage-supplies.reports.stock-ledger', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function stockLedgerItemsPicker(Request $request)
    {
        $items = Item::where('status', 'active')
            ->when($request->query('search'), fn ($q, $s) => $q->where('product_name', 'like', "%{$s}%"))
            ->orderBy('product_name')->limit(50)->get(['id', 'product_name']);

        return response()->json($items);
    }

    public function stockLedgerData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $narrations = [
            'grn_receipt' => 'Received via GRN',
            'grn_without_po' => 'Received without PO',
            'issue' => 'Issued to another store',
            'transfer_in' => 'Received via transfer',
            'transfer_out' => 'Transferred out',
            'return_inward' => 'Returned from another store',
            'return_in' => 'Returned from another store',
            'return_out' => 'Returned to another store',
            'return_outward' => 'Returned to supplier',
            'adjustment_add' => 'Stock adjustment (addition)',
            'adjustment_deduct' => 'Stock adjustment (deduction)',
            'opening_balance' => 'Opening balance / physical count',
        ];

        $rows = StockLedger::with('createdBy')
            ->where('subdepartment_id', $subdepartmentId)
            ->when($request->query('item_id'), fn ($q, $id) => $q->where('item_id', $id))
            ->whereBetween('moved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->orderBy('moved_at')
            ->get();

        return view('templates.storage_supplies.reports.partials.stock_ledger_table', compact('rows', 'narrations'));
    }

    // ---------- 3. Expiring Items ----------

    public function expiringItems(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.expiring_items', 'storage-supplies.reports.expiring-items', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function expiringItemsData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $withinDays = (int) $request->query('within_days', 90);

        $rows = StockBatch::with('item.unitOfMeasure')
            ->where('subdepartment_id', $subdepartmentId)
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '<=', now()->addDays($withinDays)->toDateString())
            ->orderBy('expiry_date')
            ->get();

        return view('templates.storage_supplies.reports.partials.expiring_items_table', compact('rows'));
    }

    // ---------- 4. Purchase Report ----------

    public function purchaseReport(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.purchase_report', 'storage-supplies.reports.purchase', [
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
        ]);
    }

    public function purchaseReportData(Request $request): View
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = GrnItem::with(['item.unitOfMeasure', 'batches', 'grn.supplier'])
            ->whereHas('grn', function ($q) use ($startDate, $endDate, $request) {
                $q->where('status', 'approved')
                    ->whereBetween('Delivery_Date', [$startDate, $endDate])
                    ->when($request->query('supplier_id'), fn ($q2, $s) => $q2->where('supplier_id', $s));
            })
            ->when($request->query('item_name'), fn ($q, $s) => $q->whereHas('item', fn ($q2) => $q2->where('product_name', 'ilike', "%{$s}%")))
            ->get()
            ->flatMap(function ($grnItem) {
                return $grnItem->batches->map(fn ($batch) => [
                    'item' => $grnItem->item,
                    'quantity' => $batch->quantity,
                    'amount' => $batch->quantity * $batch->buying_price,
                    'grn_no' => $grnItem->grn->Grn_Purchase_Order_ID,
                    'grn_date' => $grnItem->grn->Delivery_Date,
                    'supplier' => $grnItem->grn->supplier->supplier_name ?? '—',
                ]);
            });

        return view('templates.storage_supplies.reports.partials.purchase_report_table', compact('rows'));
    }

    // ---------- 5. GRN Report ----------

    public function grnReport(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.grn_report', 'storage-supplies.reports.grn', [
            'subdepartments' => $this->eligibleSubdepartments(),
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
        ]);
    }

    public function grnReportData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $supplierId = $request->query('supplier_id');
        $grnNo = $request->query('grn_no');
        $lpoNo = $request->query('lpo_no');

        $rows = collect();

        // Against Purchase Order
        GrnPurchaseOrder::with(['supplier', 'items.batches'])
            ->where('Sub_Department_ID', $subdepartmentId)->where('status', 'approved')
            ->whereBetween('Delivery_Date', [$startDate, $endDate])
            ->when($supplierId, fn ($q, $s) => $q->where('supplier_id', $s))
            ->when($grnNo, fn ($q, $g) => $q->where('Grn_Purchase_Order_ID', $g))
            ->when($lpoNo, fn ($q, $l) => $q->where('local_purchase_order_id', $l))
            ->get()->each(function ($grn) use ($rows) {
                $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price);
                $rows->push([
                    'created_date' => $grn->created_at, 'grn_no' => $grn->Grn_Purchase_Order_ID, 'type' => 'Against PO',
                    'supplier' => $grn->supplier->supplier_name ?? '—', 'delivery_note' => $grn->Delivery_Note_Number,
                    'delivery_date' => $grn->Delivery_Date, 'amount' => $amount,
                    'preview_url' => route('storage_supplies.grn.preview', $grn->Grn_Purchase_Order_ID),
                ]);
            });

        // Without PO
        GrnWithoutPo::with(['supplier', 'items.batches'])
            ->where('subdepartment_id', $subdepartmentId)->where('status', 'approved')
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->when($supplierId, fn ($q, $s) => $q->where('supplier_id', $s))
            ->when($grnNo, fn ($q, $g) => $q->where('id', $g))
            ->get()->each(function ($grn) use ($rows) {
                $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price);
                $rows->push([
                    'created_date' => $grn->created_at, 'grn_no' => $grn->id, 'type' => 'Without PO',
                    'supplier' => $grn->supplier->supplier_name ?? '—', 'delivery_note' => $grn->delivery_note_number,
                    'delivery_date' => $grn->delivery_date, 'amount' => $amount,
                    'preview_url' => route('storage_supplies.grn_without_po.preview', $grn->id),
                ]);
            });

        // Open Balance — no supplier/delivery note concept
        if (! $supplierId) {
            GrnOpenBalance::with('items.batches')
                ->where('subdepartment_id', $subdepartmentId)->where('status', 'approved')
                ->whereBetween('creation_date', [$startDate, $endDate])
                ->when($grnNo, fn ($q, $g) => $q->where('id', $g))
                ->get()->each(function ($grn) use ($rows) {
                    $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price);
                    $rows->push([
                        'created_date' => $grn->created_at, 'grn_no' => $grn->id, 'type' => 'Open Balance',
                        'supplier' => '—', 'delivery_note' => '—',
                        'delivery_date' => $grn->creation_date, 'amount' => $amount,
                        'preview_url' => route('storage_supplies.grn_open_balance.preview', $grn->id),
                    ]);
                });
        }

        // Against Issue Note — no supplier/delivery note concept
        if (! $supplierId) {
            GrnAgainstIssueNote::with('items.allocations.stockBatch')
                ->whereHas('issueNote.requisition', fn ($q) => $q->where('requesting_subdepartment_id', $subdepartmentId))
                ->where('status', 'approved')
                ->whereBetween('receipt_date', [$startDate, $endDate])
                ->when($grnNo, fn ($q, $g) => $q->where('id', $g))
                ->get()->each(function ($grn) use ($rows) {
                    $amount = $grn->items->flatMap->allocations->sum(fn ($a) => $a->quantity_allocated * ($a->stockBatch->buying_price ?? 0));
                    $rows->push([
                        'created_date' => $grn->created_at, 'grn_no' => $grn->id, 'type' => 'Against Issue Note',
                        'supplier' => '—', 'delivery_note' => '—',
                        'delivery_date' => $grn->receipt_date, 'amount' => $amount,
                        'preview_url' => route('storage_supplies.grn_against_issue_note.preview', $grn->id),
                    ]);
                });
        }

        $rows = $rows->sortByDesc('created_date')->values();

        return view('templates.storage_supplies.reports.partials.grn_report_table', compact('rows'));
    }

    // ---------- 6. Batch Management ----------

    public function batchManagement(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.batch_management', 'storage-supplies.reports.batch-management', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function batchManagementItemsPicker(Request $request)
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));

        $items = Item::whereHas('unitOfMeasure')
            ->whereIn('id', StockBatch::where('subdepartment_id', $subdepartmentId)->where('quantity_remaining', '>', 0)->distinct()->pluck('item_id'))
            ->when($request->query('search'), fn ($q, $s) => $q->where('product_name', 'ilike', "%{$s}%"))
            ->orderBy('product_name')->limit(50)->get(['id', 'product_name']);

        return response()->json($items);
    }

    public function batchManagementData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        if (! $request->query('item_id')) {
            // No item chosen — show a summary list to pick from.
            $summary = Item::with('unitOfMeasure')
                ->whereIn('id', StockBatch::where('subdepartment_id', $subdepartmentId)->where('quantity_remaining', '>', 0)->distinct()->pluck('item_id'))
                ->get()->map(fn ($item) => [
                    'item' => $item,
                    'batch_count' => StockBatch::where('subdepartment_id', $subdepartmentId)->where('item_id', $item->id)->where('quantity_remaining', '>', 0)->count(),
                    'total_balance' => StockBatch::where('subdepartment_id', $subdepartmentId)->where('item_id', $item->id)->sum('quantity_remaining'),
                ]);

            return view('templates.storage_supplies.reports.partials.batch_management_summary', compact('summary'));
        }

        $item = Item::with('unitOfMeasure')->findOrFail($request->query('item_id'));

        $batches = StockBatch::where('subdepartment_id', $subdepartmentId)
            ->where('item_id', $item->id)
            ->where('quantity_remaining', '>', 0)
            ->when($request->query('expiry_date'), fn ($q, $d) => $q->whereDate('expiry_date', $d))
            ->when($request->query('batch_number'), fn ($q, $b) => $q->where('batch_number', 'ilike', "%{$b}%"))
            ->orderBy('expiry_date')
            ->get();

        return view('templates.storage_supplies.reports.partials.batch_management_table', compact('item', 'batches'));
    }

    // ---------- 7. Store Issuing Report ----------

    public function storeIssuing(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.store_issuing', 'storage-supplies.reports.store-issuing', [
            'subdepartments' => $this->allStorageSubdepartments(),
        ]);
    }

    public function storeIssuingData(Request $request): View
    {
        $startDate = $request->query('start_date', now()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = IssueNoteItem::with(['item.unitOfMeasure', 'issueNote.requisition.requestingSubdepartment', 'issueNote.requisition.issuingSubdepartment', 'issueNote.officer'])
            ->whereHas('issueNote', function ($q) use ($startDate, $endDate, $request) {
                $q->where('status', 'approved')->whereBetween('issue_date', [$startDate, $endDate])
                    ->whereHas('requisition', function ($q2) use ($request) {
                        $q2->when($request->query('store_received_id'), fn ($q3, $s) => $q3->where('requesting_subdepartment_id', $s))
                            ->when($request->query('store_issue_id'), fn ($q3, $s) => $q3->where('issuing_subdepartment_id', $s));
                    });
            })
            ->when($request->query('item_name'), fn ($q, $s) => $q->whereHas('item', fn ($q2) => $q2->where('product_name', 'ilike', "%{$s}%")))
            ->get()
            ->map(function ($line) {
                $avgPrice = StockBatch::where('item_id', $line->item_id)->avg('buying_price') ?? 0;

                return [
                    'item' => $line->item,
                    'requesting' => $line->issueNote->requisition->requestingSubdepartment->Subdepartment_Name ?? '—',
                    'issuing' => $line->issueNote->requisition->issuingSubdepartment->Subdepartment_Name ?? '—',
                    'requisition_no' => $line->issueNote->requisition_id,
                    'issue_no' => $line->issueNote->id,
                    'quantity_requested' => $line->quantity_requested,
                    'quantity_issued' => $line->quantity_issued,
                    'buying_price' => $avgPrice,
                    'amount' => $avgPrice * $line->quantity_issued,
                    'issue_date' => $line->issueNote->issue_date,
                    'issued_by' => $line->issueNote->officer->name ?? '—',
                    'receiving_officer' => $line->issueNote->requisition->officer->name ?? '—',
                ];
            });

        return view('templates.storage_supplies.reports.partials.store_issuing_table', compact('rows'));
    }

    // ---------- 8. Quantity Issuing Report (all stores, pivoted) ----------

    public function quantityIssuing(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.quantity_issuing', 'storage-supplies.reports.quantity-issuing', []);
    }

    public function quantityIssuingData(Request $request): View
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $stores = $this->allStorageSubdepartments();

        $items = Item::when($request->query('item_name'), fn ($q, $s) => $q->where('product_name', 'ilike', "%{$s}%"))
            ->orderBy('product_name')->get();

        $rows = $items->map(function ($item) use ($stores, $startDate, $endDate) {
            $perStore = $stores->mapWithKeys(function ($store) use ($item, $startDate, $endDate) {
                $qty = StockLedger::where('item_id', $item->id)->where('subdepartment_id', $store->Subdepartment_ID)
                    ->where('movement_type', 'transfer_in')
                    ->whereBetween('moved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                    ->sum('quantity_in');

                return [$store->Subdepartment_ID => $qty];
            });

            return ['item' => $item, 'per_store' => $perStore];
        });

        return view('templates.storage_supplies.reports.partials.quantity_issuing_table', compact('rows', 'stores'));
    }

    // ---------- 9. Store Balance Report (all stores, pivoted) ----------

    public function storeBalance(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.store_balance', 'storage-supplies.reports.store-balance', []);
    }

    public function storeBalanceData(Request $request): View
    {
        $stores = $this->allStorageSubdepartments();

        $items = Item::when($request->query('item_name'), fn ($q, $s) => $q->where('product_name', 'ilike', "%{$s}%"))
            ->orderBy('product_name')->get();

        $rows = $items->map(function ($item) use ($stores) {
            $perStore = $stores->mapWithKeys(function ($store) use ($item) {
                $balance = ItemStockBalance::where('item_id', $item->id)->where('subdepartment_id', $store->Subdepartment_ID)->value('quantity_balance') ?? 0;

                return [$store->Subdepartment_ID => $balance];
            });

            return ['item' => $item, 'per_store' => $perStore];
        });

        return view('templates.storage_supplies.reports.partials.store_balance_table', compact('rows', 'stores'));
    }

    // ---------- 10. Dormant / Slow-Moving Items ----------

    public function dormantItems(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.dormant_items', 'storage-supplies.reports.dormant-items', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function dormantItemsData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $withinDays = (int) $request->query('within_days', 90);
        $cutoff = now()->subDays($withinDays);

        $balances = ItemStockBalance::with('item.unitOfMeasure')
            ->where('subdepartment_id', $subdepartmentId)
            ->where('quantity_balance', '>', 0)
            ->get();

        $rows = $balances->map(function ($balance) use ($subdepartmentId, $cutoff) {
            $lastMovement = StockLedger::where('item_id', $balance->item_id)
                ->where('subdepartment_id', $subdepartmentId)
                ->orderByDesc('moved_at')
                ->first();

            return [
                'item' => $balance->item,
                'balance' => $balance->quantity_balance,
                'last_movement' => $lastMovement?->moved_at,
                'days_since' => $lastMovement ? (int) round(now()->diffInDays($lastMovement->moved_at)) : null,
            ];
        })->filter(fn ($row) => ! $row['last_movement'] || $row['last_movement'] < $cutoff)
            ->sortByDesc(fn ($row) => $row['days_since'] ?? PHP_INT_MAX)
            ->values();

        return view('templates.storage_supplies.reports.partials.dormant_items_table', compact('rows', 'withinDays'));
    }

    // ---------- 11. Requisition Fulfillment ----------

    public function requisitionFulfillment(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.requisition_fulfillment', 'storage-supplies.reports.requisition-fulfillment', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function requisitionFulfillmentData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $requisitions = Requisition::with(['items.item.unitOfMeasure'])
            ->where('requesting_subdepartment_id', $subdepartmentId)
            ->where('status', 'approved')
            ->whereBetween('requisition_date', [$startDate, $endDate])
            ->orderByDesc('id')
            ->get();

        $rows = collect();

        foreach ($requisitions as $requisition) {
            foreach ($requisition->items as $reqItem) {
                $issueNoteItem = \App\Models\IssueNoteItem::where('requisition_item_id', $reqItem->id)->first();
                $quantityIssued = $issueNoteItem?->quantity_issued;

                $quantityReceived = null;
                if ($issueNoteItem) {
                    $grn = \App\Models\GrnAgainstIssueNote::where('issue_note_id', $issueNoteItem->issue_note_id)
                        ->where('status', 'approved')->first();

                    if ($grn) {
                        $quantityReceived = \App\Models\GrnAgainstIssueNoteItem::where('grn_id', $grn->id)
                            ->where('item_id', $reqItem->item_id)->value('quantity');
                    }
                }

                $requested = $reqItem->quantity_requested;
                $received = $quantityReceived ?? 0;
                $shortfallPct = $requested > 0 ? round((($requested - $received) / $requested) * 100, 1) : 0;

                $rows->push([
                    'requisition_no' => $requisition->id,
                    'item' => $reqItem->item,
                    'requested' => $requested,
                    'issued' => $quantityIssued,
                    'received' => $quantityReceived,
                    'shortfall_pct' => $shortfallPct,
                ]);
            }
        }

        return view('templates.storage_supplies.reports.partials.requisition_fulfillment_table', compact('rows'));
    }

    // ---------- 12. Approval Turnaround ----------

    public function approvalTurnaround(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.approval_turnaround', 'storage-supplies.reports.approval-turnaround', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function approvalTurnaroundData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $definitions = [
            ['label' => 'Store Ordering', 'model' => \App\Models\StoreRequisition::class, 'sub_col' => 'subdepartment_id', 'submitted_col' => null, 'approver' => 'approvedBy'],
            ['label' => 'GRN Against PO', 'model' => \App\Models\GrnPurchaseOrder::class, 'sub_col' => 'Sub_Department_ID', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
            ['label' => 'GRN Without PO', 'model' => \App\Models\GrnWithoutPo::class, 'sub_col' => 'subdepartment_id', 'submitted_col' => null, 'approver' => 'approvedBy'],
            ['label' => 'GRN Open Balance', 'model' => \App\Models\GrnOpenBalance::class, 'sub_col' => 'subdepartment_id', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
            ['label' => 'Requisition', 'model' => \App\Models\Requisition::class, 'sub_col' => 'requesting_subdepartment_id', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
            ['label' => 'Issue Note', 'model' => \App\Models\IssueNote::class, 'sub_col' => null, 'submitted_col' => null, 'approver' => 'approvedBy'],
            ['label' => 'GRN Against Issue Note', 'model' => \App\Models\GrnAgainstIssueNote::class, 'sub_col' => null, 'submitted_col' => null, 'approver' => 'approvedBy'],
            ['label' => 'Store Transfer', 'model' => \App\Models\StoreTransfer::class, 'sub_col' => 'from_subdepartment_id', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
            ['label' => 'Return Inward', 'model' => \App\Models\Return_::class, 'sub_col' => 'from_subdepartment_id', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
            ['label' => 'Return Outward', 'model' => \App\Models\ReturnOutward::class, 'sub_col' => 'subdepartment_id', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
            ['label' => 'Stock Adjustment', 'model' => \App\Models\StockAdjustment::class, 'sub_col' => 'subdepartment_id', 'submitted_col' => 'submitted_at', 'approver' => 'approvedBy'],
        ];

        $rows = collect();

        foreach ($definitions as $def) {
            $query = $def['model']::with($def['approver'])
                ->whereNotNull('approved_at')
                ->whereBetween('approved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);

            if ($def['sub_col']) {
                $query->where($def['sub_col'], $subdepartmentId);
            } elseif ($def['label'] === 'Issue Note') {
                $query->whereHas('requisition', fn ($q) => $q->where('issuing_subdepartment_id', $subdepartmentId));
            } elseif ($def['label'] === 'GRN Against Issue Note') {
                $query->whereHas('issueNote.requisition', fn ($q) => $q->where('requesting_subdepartment_id', $subdepartmentId));
            }

            foreach ($query->get() as $doc) {
                $start = $def['submitted_col'] ? ($doc->{$def['submitted_col']} ?? $doc->created_at) : $doc->created_at;
                $start = $start ? Carbon::parse($start) : null;
                $approved = $doc->approved_at ? Carbon::parse($doc->approved_at) : null;
                $hours = $start && $doc->approved_at ? round($start->diffInMinutes($doc->approved_at) / 60, 1) : null;

                $rows->push([
                    'type' => $def['label'],
                    'doc_no' => $doc->getKey(),
                    'started_at' => $start,
                    'approved_at' => $doc->approved_at,
                    'approver' => $doc->{$def['approver']}?->name ?? '—',
                    'hours' => $hours,
                ]);
            }
        }

        $rows = $rows->sortByDesc('approved_at')->values();
        $averageByType = $rows->groupBy('type')->map(fn ($group) => round($group->avg('hours'), 1));

        return view('templates.storage_supplies.reports.partials.approval_turnaround_table', compact('rows', 'averageByType'));
    }

    // ---------- 13. Consumption Trend ----------

    public function consumptionTrend(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.consumption_trend', 'storage-supplies.reports.consumption-trend', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function consumptionTrendData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $monthsBack = (int) $request->query('months_back', 6);
        $months = collect(range($monthsBack - 1, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));

        $items = Item::when($request->query('item_name'), fn ($q, $s) => $q->where('product_name', 'ilike', "%{$s}%"))
            ->orderBy('product_name')->get();

        $ledger = StockLedger::where('subdepartment_id', $subdepartmentId)
            ->whereIn('movement_type', ['issue', 'transfer_out'])
            ->where('moved_at', '>=', now()->subMonths($monthsBack)->startOfMonth())
            ->get()
            ->groupBy('item_id');

        $rows = $items->map(function ($item) use ($ledger, $months) {
            $itemLedger = $ledger->get($item->id, collect());

            $perMonth = $months->mapWithKeys(function ($month) use ($itemLedger) {
                $qty = $itemLedger->filter(fn ($row) => $row->moved_at->format('Y-m') === $month)->sum('quantity_out');
                return [$month => $qty];
            });

            return ['item' => $item, 'per_month' => $perMonth, 'total' => $perMonth->sum()];
        })->filter(fn ($row) => $row['total'] > 0)->values();

        return view('templates.storage_supplies.reports.partials.consumption_trend_table', compact('rows', 'months'));
    }

    // ---------- 14. Wastage / Loss ----------

    public function wastageLoss(): View
    {
        return $this->nicePage('templates.storage_supplies.reports.wastage_loss', 'storage-supplies.reports.wastage-loss', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function wastageLossData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = \App\Models\StockAdjustmentItem::with('item.unitOfMeasure')
            ->whereHas('adjustment', function ($q) use ($subdepartmentId, $startDate, $endDate) {
                $q->where('subdepartment_id', $subdepartmentId)
                    ->where('status', 'approved')
                    ->where('reason', 'expired_dump_broken')
                    ->whereBetween('adjustment_date', [$startDate, $endDate]);
            })
            ->get()
            ->map(function ($line) use ($subdepartmentId) {
                $avgPrice = StockBatch::where('item_id', $line->item_id)->where('subdepartment_id', $subdepartmentId)->avg('buying_price') ?? 0;

                return [
                    'item' => $line->item,
                    'quantity' => $line->quantity,
                    'value' => $line->quantity * $avgPrice,
                ];
            });

        $totalValue = $rows->sum('value');

        return view('templates.storage_supplies.reports.partials.wastage_loss_table', compact('rows', 'totalValue'));
    }
}
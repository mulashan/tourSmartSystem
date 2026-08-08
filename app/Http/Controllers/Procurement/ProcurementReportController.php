<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\GrnPurchaseOrder;
use App\Models\Item;
use App\Models\LocalPurchaseOrder;
use App\Models\LocalPurchaseOrderItem;
use App\Models\Lookup;
use App\Models\StoreRequisition;
use App\Models\Subdepartment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class ProcurementReportController extends Controller
{
    private function eligibleSubdepartments()
    {
        $user = User::with('subdepartments.department.departmentNature')->find(session('user_id'));

        return ($user?->subdepartments ?? collect())
            ->filter(fn ($sub) => optional($sub->department?->departmentNature)->department_nature === 'Procurements')
            ->sortBy('Subdepartment_Name')
            ->values();
    }

    private function assertEligibleSubdepartment(int $subdepartmentId): void
    {
        abort_unless($this->eligibleSubdepartments()->contains('Subdepartment_ID', $subdepartmentId), Response::HTTP_FORBIDDEN, 'You are not assigned to that Sub Department.');
    }

    // ---------- 1. Purchasing History ----------

    public function purchasingHistory(): View
    {
        return $this->nicePage('templates.procurement.reports.purchasing_history', 'procurement.reports.purchasing-history', [
            'subdepartments' => $this->eligibleSubdepartments(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function purchasingHistoryData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = GrnPurchaseOrder::with(['supplier', 'items.item', 'items.batches', 'localPurchaseOrder'])
            ->where('status', 'approved')
            ->whereHas('localPurchaseOrder', fn ($q) => $q->where('procurement_subdepartment_id', $subdepartmentId))
            ->whereBetween('Delivery_Date', [$startDate, $endDate])
            ->get()
            ->flatMap(function ($grn) use ($request) {
                return $grn->items
                    ->when($request->query('item_category_id'), fn ($items, $cat) => $items->filter(fn ($i) => $i->item?->item_category_id == $cat))
                    ->when($request->query('item_name'), fn ($items, $name) => $items->filter(fn ($i) => stripos($i->item?->product_name ?? '', $name) !== false))
                    ->flatMap(function ($grnItem) use ($grn) {
                        return $grnItem->batches->map(fn ($batch) => [
                            'item' => $grnItem->item,
                            'supplier' => $grn->supplier->supplier_name ?? '—',
                            'purchase_date' => $grn->Delivery_Date,
                            'buying_price' => $batch->buying_price,
                            'quantity' => $batch->quantity,
                            'grn_no' => $grn->Grn_Purchase_Order_ID,
                            'document_type' => 'Local Purchase Order',
                        ]);
                    });
            })
            ->sortByDesc('purchase_date')
            ->values();

        return view('templates.procurement.reports.partials.purchasing_history_table', compact('rows'));
    }

    // ---------- 2. Previous Purchase Requisition ----------

    public function previousPurchaseRequisition(): View
    {
        return $this->nicePage('templates.procurement.reports.previous_purchase_requisition', 'procurement.reports.previous-purchase-requisition', [
            'subdepartments' => $this->eligibleSubdepartments(),
            'suppliers' => Supplier::orderBy('supplier_name')->get(),
        ]);
    }

    public function previousPurchaseRequisitionData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = LocalPurchaseOrder::with(['storeRequisition.subdepartment', 'supplier', 'createdBy', 'grn'])
            ->where('procurement_subdepartment_id', $subdepartmentId)
            ->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->when($request->query('store_requesting_id'), fn ($q, $s) => $q->whereHas('storeRequisition', fn ($q2) => $q2->where('subdepartment_id', $s)))
            ->when($request->query('supplier_id'), fn ($q, $s) => $q->where('supplier_id', $s))
            ->orderByDesc('local_purchase_order_id')
            ->get();

        return view('templates.procurement.reports.partials.previous_purchase_requisition_table', compact('rows'));
    }

    // ---------- 3. Received GRN ----------

    public function receivedGrn(): View
    {
        return $this->nicePage('templates.procurement.reports.received_grn', 'procurement.reports.received-grn', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function receivedGrnData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = GrnPurchaseOrder::with(['supplier', 'createdBy', 'localPurchaseOrder.storeRequisition.subdepartment', 'items.batches'])
            ->where('status', 'approved')
            ->whereHas('localPurchaseOrder', fn ($q) => $q->where('procurement_subdepartment_id', $subdepartmentId))
            ->whereBetween('Delivery_Date', [$startDate, $endDate])
            ->when($request->query('store_requesting_id'), fn ($q, $s) => $q->whereHas('localPurchaseOrder.storeRequisition', fn ($q2) => $q2->where('subdepartment_id', $s)))
            ->when($request->query('lpo_no'), fn ($q, $l) => $q->where('local_purchase_order_id', $l))
            ->orderByDesc('Grn_Purchase_Order_ID')
            ->get()
            ->map(function ($grn) {
                $amount = $grn->items->flatMap->batches->sum(fn ($b) => $b->quantity * $b->buying_price);

                return [
                    'lpo_no' => $grn->local_purchase_order_id,
                    'order_no' => $grn->localPurchaseOrder?->store_requisition_id,
                    'order_created_by' => $grn->localPurchaseOrder?->storeRequisition?->preparedBy?->name ?? '—',
                    'delivery_date' => $grn->Delivery_Date,
                    'store_requesting' => $grn->localPurchaseOrder?->storeRequisition?->subdepartment?->Subdepartment_Name ?? '—',
                    'supplier' => $grn->supplier->supplier_name ?? '—',
                    'delivery_note' => $grn->Delivery_Note_Number,
                    'invoice_no' => $grn->Invoice_Number,
                    'created_by' => $grn->createdBy->name ?? '—',
                    'amount' => $amount,
                    'preview_url' => route('storage_supplies.grn.preview', $grn->Grn_Purchase_Order_ID),
                ];
            });

        return view('templates.procurement.reports.partials.received_grn_table', compact('rows'));
    }

    // ---------- 4. Procurement Report ----------

    public function procurementReport(): View
    {
        return $this->nicePage('templates.procurement.reports.procurement_report', 'procurement.reports.procurement-report', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function procurementReportData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $lpos = LocalPurchaseOrder::with(['storeRequisition.subdepartment', 'items'])
            ->where('procurement_subdepartment_id', $subdepartmentId)
            ->where('status', 'approved')
            ->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->get();

        $rows = $lpos->groupBy(fn ($lpo) => $lpo->storeRequisition?->subdepartment?->Subdepartment_Name ?? 'Unknown Store')
            ->map(function ($group, $storeName) {
                $amount = $group->sum(function ($lpo) {
                    $itemsTotal = $lpo->items->sum(fn ($i) => $i->Quantity_Required * $i->Price);
                    $otherSum = collect([$lpo->vat_charges, $lpo->transport_charges, $lpo->labor_charges, $lpo->bank_charges, $lpo->freight_charges, $lpo->other_charges])
                        ->map(fn ($v) => (float) $v)->sum();

                    return $itemsTotal + $otherSum;
                });

                return [
                    'store' => $storeName,
                    'requisition_numbers' => $group->pluck('local_purchase_order_id')->implode(', '),
                    'amount' => $amount,
                ];
            })
            ->values();

        return view('templates.procurement.reports.partials.procurement_report_table', compact('rows'));
    }

    // ---------- 5. Cancelled Purchase Requisition ----------

    public function cancelledPurchaseRequisition(): View
    {
        return $this->nicePage('templates.procurement.reports.cancelled_purchase_requisition', 'procurement.reports.cancelled-purchase-requisition', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function cancelledPurchaseRequisitionData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = StoreRequisition::with(['subdepartment', 'preparedBy', 'cancelledBy'])
            ->where('procurement_status', 'rejected')
            ->where('procurement_subdepartment_id', $subdepartmentId)
            ->whereBetween('cancelled_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->orderByDesc('cancelled_at')
            ->get();

        return view('templates.procurement.reports.partials.cancelled_purchase_requisition_table', compact('rows'));
    }

    // ---------- 6. Last Buying Price ----------

    public function lastBuyingPrice(): View
    {
        return $this->nicePage('templates.procurement.reports.last_buying_price', 'procurement.reports.last-buying-price', [
            'subdepartments' => $this->eligibleSubdepartments(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function lastBuyingPriceData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $items = Item::with('unitOfMeasure')
            ->when($request->query('item_category_id'), fn ($q, $c) => $q->where('item_category_id', $c))
            ->when($request->query('item_name'), fn ($q, $s) => $q->where('product_name', 'ilike', "%{$s}%"))
            ->orderBy('product_name')
            ->get();

        $rows = $items->map(function ($item) use ($subdepartmentId) {
            $lastLine = LocalPurchaseOrderItem::with('lpo.supplier')
                ->where('Item_ID', $item->id)
                ->whereHas('lpo', fn ($q) => $q->where('procurement_subdepartment_id', $subdepartmentId)->where('status', 'approved'))
                ->orderByDesc('created_at')
                ->first();

            return [
                'item' => $item,
                'last_price' => $lastLine?->Price,
                'supplier' => $lastLine?->lpo?->supplier?->supplier_name,
                'purchase_date' => $lastLine?->created_at,
                'lpo_no' => $lastLine?->local_purchase_order_id,
            ];
        })->filter(fn ($row) => $row['last_price'] !== null)->values();

        return view('templates.procurement.reports.partials.last_buying_price_table', compact('rows'));
    }

    // ---------- 7. Pending Purchase Orders Aging ----------

    public function pendingPoAging(): View
    {
        return $this->nicePage('templates.procurement.reports.pending_po_aging', 'procurement.reports.pending-po-aging', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function pendingPoAgingData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $rows = LocalPurchaseOrder::with(['storeRequisition.subdepartment', 'supplier', 'createdBy'])
            ->where('procurement_subdepartment_id', $subdepartmentId)
            ->whereIn('status', ['draft', 'pending_approval'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->get()
            ->map(function ($lpo) {
                $started = $lpo->submitted_at ?? $lpo->created_at;
                $hours = $started ? round(Carbon::parse($started)->diffInMinutes(now()) / 60, 1) : null;
                //$hours = round($started->diffInMinutes(now()) / 60, 1);

                return [
                    'lpo' => $lpo,
                    'started_at' => $started,
                    'hours' => $hours,
                    'days' => round($hours / 24, 1),
                ];
            })
            ->sortByDesc('hours')
            ->values();

        return view('templates.procurement.reports.partials.pending_po_aging_table', compact('rows'));
    }

    // ---------- 8. Supplier Price Trend ----------

    public function supplierPriceTrend(): View
    {
        return $this->nicePage('templates.procurement.reports.supplier_price_trend', 'procurement.reports.supplier-price-trend', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function supplierPriceTrendItemsPicker(Request $request)
    {
        $items = Item::where('status', 'active')
            ->when($request->query('search'), fn ($q, $s) => $q->where('product_name', 'ilike', "%{$s}%"))
            ->orderBy('product_name')->limit(50)->get(['id', 'product_name']);

        return response()->json($items);
    }

    public function supplierPriceTrendData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $itemId = $request->query('item_id');
        $jumpThresholdPct = (float) $request->query('jump_threshold_pct', 15);

        if (! $itemId) {
            return view('templates.procurement.reports.partials.supplier_price_trend_table', ['item' => null, 'rows' => collect(), 'jumpThresholdPct' => $jumpThresholdPct]);
        }

        $item = Item::with('unitOfMeasure')->findOrFail($itemId);

        $lines = LocalPurchaseOrderItem::with('lpo.supplier')
            ->where('Item_ID', $itemId)
            ->whereHas('lpo', fn ($q) => $q->where('procurement_subdepartment_id', $subdepartmentId)->where('status', 'approved'))
            ->orderBy('created_at')
            ->get();

        $rows = collect();
        $previousPrice = null;

        foreach ($lines as $line) {
            $changePct = $previousPrice ? round((($line->Price - $previousPrice) / $previousPrice) * 100, 1) : null;

            $rows->push([
                'date' => $line->created_at,
                'supplier' => $line->lpo->supplier->supplier_name ?? '—',
                'lpo_no' => $line->local_purchase_order_id,
                'price' => $line->Price,
                'change_pct' => $changePct,
                'flagged' => $changePct !== null && abs($changePct) >= $jumpThresholdPct,
            ]);

            $previousPrice = $line->Price;
        }

        return view('templates.procurement.reports.partials.supplier_price_trend_table', compact('item', 'rows', 'jumpThresholdPct'));
    }

    // ---------- 9. Requisition Rejection Rate ----------

    public function requisitionRejectionRate(): View
    {
        return $this->nicePage('templates.procurement.reports.requisition_rejection_rate', 'procurement.reports.requisition-rejection-rate', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function requisitionRejectionRateData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $requisitions = StoreRequisition::with('subdepartment')
            ->where('status', 'approved') // approved at store level, i.e. reached procurement
            ->whereIn('procurement_status', ['rejected', 'ordered', 'pending'])
            ->whereBetween('approved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->get();

        $rows = $requisitions->groupBy(fn ($r) => $r->subdepartment?->Subdepartment_Name ?? 'Unknown Store')
            ->map(function ($group, $storeName) {
                $total = $group->count();
                $rejected = $group->where('procurement_status', 'rejected')->count();
                $processed = $group->whereIn('procurement_status', ['rejected', 'ordered'])->count();

                return [
                    'store' => $storeName,
                    'total' => $total,
                    'rejected' => $rejected,
                    'processed' => $processed,
                    'rejection_rate' => $processed > 0 ? round(($rejected / $processed) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('rejection_rate')
            ->values();

        return view('templates.procurement.reports.partials.requisition_rejection_rate_table', compact('rows'));
    }

    // ---------- 10. Top Suppliers by Spend ----------

    public function topSuppliersBySpend(): View
    {
        return $this->nicePage('templates.procurement.reports.top_suppliers_by_spend', 'procurement.reports.top-suppliers-by-spend', [
            'subdepartments' => $this->eligibleSubdepartments(),
        ]);
    }

    public function topSuppliersBySpendData(Request $request): View
    {
        $subdepartmentId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subdepartmentId);

        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $lpos = LocalPurchaseOrder::with(['supplier', 'items'])
            ->where('procurement_subdepartment_id', $subdepartmentId)
            ->where('status', 'approved')
            ->whereBetween('approved_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->get();

        $rows = $lpos->groupBy(fn ($lpo) => $lpo->supplier->supplier_name ?? 'Unknown Supplier')
            ->map(function ($group, $supplierName) {
                $totalSpend = $group->sum(function ($lpo) {
                    $itemsTotal = $lpo->items->sum(fn ($i) => $i->Quantity_Required * $i->Price);
                    $otherSum = collect([$lpo->vat_charges, $lpo->transport_charges, $lpo->labor_charges, $lpo->bank_charges, $lpo->freight_charges, $lpo->other_charges])
                        ->map(fn ($v) => (float) $v)->sum();

                    return $itemsTotal + $otherSum;
                });

                return [
                    'supplier' => $supplierName,
                    'lpo_count' => $group->count(),
                    'total_spend' => $totalSpend,
                ];
            })
            ->sortByDesc('total_spend')
            ->values();

        $grandTotal = $rows->sum('total_spend');

        return view('templates.procurement.reports.partials.top_suppliers_by_spend_table', compact('rows', 'grandTotal'));
    }
}
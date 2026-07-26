<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Lookup;
use App\Models\StoreRequisition;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\UserPrivilege;

class StoreOrderController extends Controller
{
    public function newOrder(): View|RedirectResponse
    {
        return $this->nicePage('templates.storage_supplies.store_ordering.new_order', 'storage-supplies.store-ordering.new-order', [
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
            'nextOrderNumberPreview' => (int) (StoreRequisition::max('id') ?? 0) + 1,
            'preparedByName' => session('user_name'),
        ]);
    }

    public function itemsPicker(Request $request): JsonResponse
    {
        $subdepartmentId = session('active_subdepartment_id');

        $items = Item::query()
            ->with('unitOfMeasure')
            ->where('status', 'active')
            ->when($request->query('category_id'), fn ($q, $categoryId) => $q->where('item_category_id', $categoryId))
            ->when($request->query('search'), fn ($q, $search) => $q->where('product_name', 'like', "%{$search}%"))
            ->orderBy('product_name')
            ->limit(100)
            ->get(['id', 'product_name', 'unit_of_measure_id']);

        $balances = \App\Models\ItemStockBalance::where('subdepartment_id', $subdepartmentId)
            ->whereIn('item_id', $items->pluck('id'))
            ->pluck('quantity_balance', 'item_id');

        return response()->json($items->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->product_name,
            'uom' => $item->unitOfMeasure->name ?? '',
            'balance' => $balances->get($item->id, 0),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'priority_status' => 'required|in:normal,emergency',
            'emergency_reason' => 'nullable|string|max:255',
            'order_description' => 'nullable|string|max:255',
            'is_user_store_order' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.units' => 'required|integer|min:1',
            'items.*.items_per_unit' => 'required|integer|min:1',
            'items.*.item_details' => 'nullable|string|max:255',
        ]);

        $itemIds = collect($data['items'])->pluck('item_id');
            if ($itemIds->count() !== $itemIds->unique()->count()) {
                return response()->json(['message' => 'Duplicate items are not allowed in a single order.'], 422);
            }

        $subdepartmentId = session('active_subdepartment_id');
        abort_unless($subdepartmentId, 419, 'No active Sub Department in session.');

        $requisition = DB::transaction(function () use ($data, $subdepartmentId) {
            $requisition = StoreRequisition::create([
                'order_date' => now()->toDateString(),
                'subdepartment_id' => $subdepartmentId,
                'prepared_by_user_id' => session('user_id'),
                'priority_status' => $data['priority_status'],
                'emergency_reason' => $data['emergency_reason'] ?? null,
                'order_description' => $data['order_description'] ?? null,
                'is_user_store_order' => $data['is_user_store_order'] ?? false,
                'status' => 'pending',
            ]);

            foreach ($data['items'] as $line) {
                $requisition->items()->create([
                    'item_id' => $line['item_id'],
                    'units' => $line['units'],
                    'items_per_unit' => $line['items_per_unit'],
                    'quantity' => $line['units'] * $line['items_per_unit'],
                    'item_details' => $line['item_details'] ?? null,
                ]);
            }

            return $requisition;
        });

        return response()->json(['success' => true, 'id' => $requisition->id]);
    }

    public function pendingOrder(): View|RedirectResponse
    {
        return $this->nicePage('templates.storage_supplies.store_ordering.pending_order', 'storage-supplies.store-ordering.pending-order', [
            'items' => StoreRequisition::with(['subdepartment', 'preparedBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, StoreRequisition $storeRequisition): JsonResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('store_ordering_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve store ordering requisitions.'], 403);
        }

        $storeRequisition->update([
            'status' => 'approved',
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function previousOrder(): View|RedirectResponse
    {
        return $this->nicePage('templates.storage_supplies.store_ordering.previous_order', 'storage-supplies.store-ordering.previous-order', [
            'items' => StoreRequisition::with(['subdepartment', 'preparedBy', 'approvedBy', 'localPurchaseOrder'])
            ->where('subdepartment_id', session('active_subdepartment_id'))
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->get(),
        ]);
    }

    public function preview(StoreRequisition $storeRequisition): View
    {
        $storeRequisition->load([
            'items.item.unitOfMeasure',
            'subdepartment.department.branch.company',
            'preparedBy',
            'approvedBy',
        ]);

        $branch = $storeRequisition->subdepartment?->department?->branch;
        $company = $branch?->company;

        $preparedByTitle = null;
        if ($storeRequisition->preparedBy?->privilege_id) {
            $preparedByTitle = UserPrivilege::find($storeRequisition->preparedBy->privilege_id)?->privilege_name;
        }

        $totals = [
            'units' => $storeRequisition->items->sum('units'),
            'items_per_unit' => $storeRequisition->items->sum('items_per_unit'),
            'quantity' => $storeRequisition->items->sum('quantity'),
        ];

        return view('templates.storage_supplies.store_ordering.preview', [
            'storeRequisition' => $storeRequisition,
            'branch' => $branch,
            'company' => $company,
            'preparedByTitle' => $preparedByTitle,
            'totals' => $totals,
            'printedByName' => session('user_name'),
        ]);
    }

    public function editItems(StoreRequisition $storeRequisition): View|RedirectResponse
    {
        abort_unless($storeRequisition->status === 'pending', 403, 'Only pending orders can be edited.');
        abort_unless($storeRequisition->subdepartment_id === session('active_subdepartment_id'), 403, 'This order belongs to a different Sub Department.');

        $storeRequisition->load('items.item.unitOfMeasure');

        return $this->nicePage('templates.storage_supplies.store_ordering.edit_order', 'storage-supplies.store-ordering.pending-order', [
            'storeRequisition' => $storeRequisition,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function updateItems(Request $request, StoreRequisition $storeRequisition): JsonResponse
    {
        abort_unless($storeRequisition->status === 'pending', 403, 'Only pending orders can be edited.');
        abort_unless($storeRequisition->subdepartment_id === session('active_subdepartment_id'), 403, 'This order belongs to a different Sub Department.');

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.units' => 'required|integer|min:1',
            'items.*.items_per_unit' => 'required|integer|min:1',
            'items.*.item_details' => 'nullable|string|max:255',
        ]);

        $itemIds = collect($data['items'])->pluck('item_id');
        if ($itemIds->count() !== $itemIds->unique()->count()) {
            return response()->json(['message' => 'Duplicate items are not allowed in a single order.'], 422);
        }

        DB::transaction(function () use ($data, $storeRequisition) {
            $storeRequisition->items()->delete();

            foreach ($data['items'] as $line) {
                $storeRequisition->items()->create([
                    'item_id' => $line['item_id'],
                    'units' => $line['units'],
                    'items_per_unit' => $line['items_per_unit'],
                    'quantity' => $line['units'] * $line['items_per_unit'],
                    'item_details' => $line['item_details'] ?? null,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

}
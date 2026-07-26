<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Lookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(): View|RedirectResponse
    {
        return $this->nicePage('templates.storage_supplies.items.item_manager', 'storage-supplies.items', [
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
            'measuringUnits' => Lookup::ofType('measuring_unit')->orderBy('name')->get(),
        ]);
    }

    public function list(Request $request): View
    {
        $items = Item::with(['itemCategory', 'unitOfMeasure'])
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('product_name')
            ->get();

        return view('templates.storage_supplies.items.partials.item_table', compact('items'));
    }

    public function store(Request $request): JsonResponse
    {
        $item = Item::create($this->validated($request));

        return response()->json(['success' => true, 'item' => $item->load(['itemCategory', 'unitOfMeasure'])]);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        $item->update($this->validated($request, $item->id));

        return response()->json(['success' => true, 'item' => $item->load(['itemCategory', 'unitOfMeasure'])]);
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json(['success' => true]);
    }

    // Refreshes the Item Category dropdown after a quick-add, without a full page reload.
    public function categoryOptions(): JsonResponse
    {
        return response()->json(
            Lookup::ofType('item_category')->orderBy('name')->get(['id', 'name'])
        );
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'product_name' => 'required|string|max:150',
            'product_code_prefix' => 'nullable|string|max:30',
            'product_code' => 'nullable|string|max:60|unique:tbl_items,product_code,' . ($ignoreId ?? 'NULL') . ',id',
            'unit_of_measure_id' => 'nullable|integer|exists:tbl_lookups,id',
            'item_category_id' => 'required|integer|exists:tbl_lookups,id',
            'status' => 'required|in:active,inactive',
            'reorder_level' => 'nullable|integer|min:0',
            'minimum_reorder_level' => 'nullable|integer|min:0',
            'maximum_reorder_level' => 'nullable|integer|min:0',
        ]);
    }
}
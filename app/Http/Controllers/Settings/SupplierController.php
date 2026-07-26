<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function list(Request $request): View
    {
        $items = Supplier::query()
            ->when($request->query('search'), fn ($q, $search) => $q->where('supplier_name', 'like', "%{$search}%"))
            ->orderBy('supplier_name')
            ->get();

        return view('templates.settings.other_settings.partials.supplier_table', compact('items'));
    }

    public function store(Request $request): JsonResponse
    {
        $supplier = Supplier::create($this->validated($request));

        return response()->json(['success' => true, 'item' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($this->validated($request, $supplier->id));

        return response()->json(['success' => true, 'item' => $supplier]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'supplier_name' => 'required|string|max:150|unique:tbl_suppliers,supplier_name,' . ($ignoreId ?? 'NULL') . ',id',
            'supplier_address' => 'required|string|max:255',
            'postal_address' => 'required|string|max:255',
            'contact_person_name' => 'required|string|max:150',
            'contact_person_mobile' => 'required|string|max:30',
            'contact_person_email' => 'required|email|max:150',
            'telephone' => 'nullable|string|max:30',
            'fax' => 'nullable|string|max:30',
            'physical_address' => 'nullable|string|max:255',
        ]);
    }
}
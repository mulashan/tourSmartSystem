<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\ItemStockBalance;
use App\Models\Lookup;
use App\Models\Requisition;
use App\Models\Subdepartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RequisitionController extends Controller
{
    public function pendingList(): View
    {
        return $this->nicePage('templates.storage_supplies.requisition.pending_list', 'storage-supplies.requisition.pending', [
            'items' => Requisition::with(['issuingSubdepartment', 'officer'])
                ->where('requesting_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'draft')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.storage_supplies.requisition.create', 'storage-supplies.requisition.new', [
            'nextDocumentNumberPreview' => (int) (Requisition::max('id') ?? 0) + 1,
            'issuingSubdepartments' => $this->eligibleIssuingSubdepartments(),
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function itemsPicker(Request $request): JsonResponse
    {
        $request->validate(['issuing_subdepartment_id' => 'required|integer']);

        $items = \App\Models\Item::query()
            ->with('unitOfMeasure')
            ->where('status', 'active')
            ->when($request->query('category_id'), fn ($q, $categoryId) => $q->where('item_category_id', $categoryId))
            ->when($request->query('search'), fn ($q, $search) => $q->where('product_name', 'ilike', "%{$search}%"))
            ->orderBy('product_name')
            ->limit(100)
            ->get(['id', 'product_name', 'unit_of_measure_id']);

        $balances = ItemStockBalance::where('subdepartment_id', $request->query('issuing_subdepartment_id'))
            ->whereIn('item_id', $items->pluck('id'))
            ->pluck('quantity_balance', 'item_id');

        // Only items with real stock at the issuing store are worth showing here.
        return response()->json($items->filter(fn ($item) => $balances->get($item->id, 0) > 0)->values()->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->product_name,
            'uom' => $item->unitOfMeasure->name ?? '',
            'balance' => $balances->get($item->id, 0),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePayload($request);

        $requisition = $this->persist($data, null);

        return response()->json(['success' => true, 'id' => $requisition->id]);
    }

    public function edit(Requisition $requisition): View
    {
        abort_unless($requisition->status === 'draft', 403, 'Only draft requisitions can be edited.');
        abort_unless($requisition->requesting_subdepartment_id === session('active_subdepartment_id'), 403);

        $requisition->load('items.item.unitOfMeasure', 'issuingSubdepartment');

        $balances = ItemStockBalance::where('subdepartment_id', $requisition->issuing_subdepartment_id)
            ->whereIn('item_id', $requisition->items->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        return $this->nicePage('templates.storage_supplies.requisition.edit', 'storage-supplies.requisition.pending', [
            'requisition' => $requisition,
            'balances' => $balances,
            'itemCategories' => Lookup::ofType('item_category')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Requisition $requisition): JsonResponse
    {
        abort_unless($requisition->status === 'draft', 403, 'Only draft requisitions can be edited.');

        // Issuing store is locked once a requisition exists — ignore anything posted for it,
        // always re-validate against the store the requisition was actually created against.
        $data = $this->validatePayload($request, $requisition->issuing_subdepartment_id);

        $this->persist($data, $requisition);

        return response()->json(['success' => true]);
    }

    private function validatePayload(Request $request, ?int $lockedIssuingSubdepartmentId = null): array
    {
        $rules = [
            'description' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:tbl_items,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
            'items.*.item_details' => 'nullable|string|max:255',
        ];

        if (! $lockedIssuingSubdepartmentId) {
            $rules['issuing_subdepartment_id'] = 'required|integer|exists:tbl_subdepartment,Subdepartment_ID';
        }

        $validated = $request->validate($rules);
        $validated['issuing_subdepartment_id'] = $lockedIssuingSubdepartmentId ?? $validated['issuing_subdepartment_id'];

        $balances = ItemStockBalance::where('subdepartment_id', $validated['issuing_subdepartment_id'])
            ->whereIn('item_id', collect($validated['items'])->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        foreach ($validated['items'] as $line) {
            $available = $balances->get($line['item_id'], 0);

            if ($line['quantity_requested'] > $available) {
                $itemName = \App\Models\Item::find($line['item_id'])?->product_name ?? "Item #{$line['item_id']}";
                abort(422, "Requested quantity for \"{$itemName}\" ({$line['quantity_requested']}) exceeds the Issuing Store's balance ({$available}).");
            }
        }

        return $validated;
    }

    private function persist(array $data, ?Requisition $requisition): Requisition
    {
        return DB::transaction(function () use ($data, $requisition) {
            if ($requisition) {
                $requisition->update(['description' => $data['description']]);
                $requisition->items()->delete();
            } else {
                $requisition = Requisition::create([
                    'requisition_date' => now()->toDateString(),
                    'requesting_subdepartment_id' => session('active_subdepartment_id'),
                    'issuing_subdepartment_id' => $data['issuing_subdepartment_id'],
                    'officer_user_id' => session('user_id'),
                    'description' => $data['description'],
                    'status' => 'draft',
                ]);
            }

            foreach ($data['items'] as $line) {
                $requisition->items()->create([
                    'item_id' => $line['item_id'],
                    'quantity_requested' => $line['quantity_requested'],
                    'item_details' => $line['item_details'] ?? null,
                ]);
            }

            return $requisition;
        });
    }

    public function submit(Requisition $requisition): JsonResponse
    {
        abort_unless($requisition->status === 'draft', 403, 'Only draft requisitions can be submitted.');
        abort_unless($requisition->requesting_subdepartment_id === session('active_subdepartment_id'), 403);

        $requisition->update([
            'status' => 'pending_approval',
            'submitted_by_user_id' => session('user_id'),
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.requisition.approve_list', 'storage-supplies.requisition.approve', [
            'items' => Requisition::with(['issuingSubdepartment', 'officer'])
                ->where('requesting_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, Requisition $requisition): JsonResponse
    {
        abort_unless($requisition->status === 'pending_approval', 403, 'Only requisitions pending approval can be approved.');
        abort_unless($requisition->requesting_subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('store_requisition_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Requisitions.'], 403);
        }

        // Approval only authorizes the request — no stock moves here. That happens once
        // the issuing store fulfills it via Issue Note, and is finalized at GRN Against Issue Note.
        $requisition->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.requisition.previous_list', 'storage-supplies.requisition.previous', [
            'items' => Requisition::with(['issuingSubdepartment', 'officer', 'approvedBy'])
                ->where('requesting_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(Requisition $requisition): View
    {
        $requisition->load(['items.item.unitOfMeasure', 'requestingSubdepartment.department.branch.company', 'issuingSubdepartment', 'officer', 'approvedBy']);

        $branch = $requisition->requestingSubdepartment?->department?->branch;

        return view('templates.storage_supplies.requisition.preview', [
            'requisition' => $requisition,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }

    private function eligibleIssuingSubdepartments()
    {
        return Subdepartment::with('department.departmentNature')
            ->where('Subdepartment_ID', '!=', session('active_subdepartment_id'))
            ->get()
            ->filter(fn ($sub) => optional($sub->department?->departmentNature)->department_nature === 'Storage and Supplies')
            ->sortBy('Subdepartment_Name')
            ->values();
    }
}
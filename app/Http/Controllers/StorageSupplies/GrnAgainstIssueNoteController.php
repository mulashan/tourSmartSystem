<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\GrnAgainstIssueNote;
use App\Models\GrnAgainstIssueNoteItem;
use App\Models\GrnBatchAllocation;
use App\Models\IssueNote;
use App\Models\ItemStockBalance;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GrnAgainstIssueNoteController extends Controller
{
    public function newList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_against_issue_note.new_list', 'storage-supplies.grn-issue.new', [
            'items' => IssueNote::with(['requisition.issuingSubdepartment', 'officer'])
                ->where('status', 'approved')
                ->whereDoesntHave('grnAgainstIssueNote')
                ->whereHas('requisition', fn ($q) => $q->where('requesting_subdepartment_id', session('active_subdepartment_id')))
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    // Computes what FEFO would allocate right now, purely for display before saving.
   public function create(IssueNote $issueNote): View
    {
        abort_if($issueNote->grnAgainstIssueNote, 409, 'A GRN already exists for this Issue Note.');
        abort_unless($issueNote->requisition->requesting_subdepartment_id === session('active_subdepartment_id'), 403);

        $issueNote->load('items.item.unitOfMeasure', 'requisition.issuingSubdepartment');

        return $this->nicePage('templates.storage_supplies.grn_against_issue_note.create', 'storage-supplies.grn-issue.new', [
            'issueNote' => $issueNote,
        ]);
    }

    // Greedy FEFO plan: oldest expiry first, until the requested quantity is covered (or stock runs out).
    private function planFefo(int $itemId, int $subdepartmentId, int $quantityNeeded): array
    {
        $batches = StockBatch::availableFefo($itemId, $subdepartmentId)->get();
        $plan = [];
        $remaining = $quantityNeeded;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $take = min($remaining, $batch->quantity_remaining);
            $plan[] = ['batch' => $batch, 'quantity' => $take];
            $remaining -= $take;
        }

        return ['plan' => $plan, 'shortfall' => max($remaining, 0)];
    }

    public function store(Request $request, IssueNote $issueNote): JsonResponse
    {
        abort_if($issueNote->grnAgainstIssueNote, 409, 'A GRN already exists for this Issue Note.');

        $data = $request->validate([
            'receipt_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.issue_note_item_id' => 'required|integer|exists:tbl_issue_note_items,id',
            'items.*.quantity_received' => 'required|integer|min:1',
        ]);

        $issueNote->load('items.item');
        $issuingSubId = $issueNote->requisition->issuing_subdepartment_id;

        $grn = DB::transaction(function () use ($issueNote, $data, $issuingSubId) {
            $grn = GrnAgainstIssueNote::create([
                'issue_note_id' => $issueNote->id,
                'created_by_user_id' => session('user_id'),
                'receipt_date' => $data['receipt_date'],
                'status' => 'pending_approval',
            ]);

            foreach ($data['items'] as $line) {
                $noteItem = $issueNote->items->firstWhere('id', $line['issue_note_item_id']);
                abort_unless($noteItem, 422, 'Invalid item reference.');

                if ($line['quantity_received'] > $noteItem->quantity_issued) {
                    abort(422, "Quantity received for \"{$noteItem->item->product_name}\" cannot exceed quantity issued ({$noteItem->quantity_issued}).");
                }

                $grnItem = GrnAgainstIssueNoteItem::create([
                    'grn_id' => $grn->id,
                    'item_id' => $noteItem->item_id,
                    'quantity' => $line['quantity_received'],
                ]);

                // Batch allocation is entirely internal — nobody picks batches, FEFO decides.
                $result = $this->planFefo($noteItem->item_id, $issuingSubId, $line['quantity_received']);

                if ($result['shortfall'] > 0) {
                    abort(422, "Insufficient batch stock for \"{$noteItem->item->product_name}\" at the issuing store — {$result['shortfall']} unit(s) short.");
                }

                foreach ($result['plan'] as $allocation) {
                    GrnBatchAllocation::create([
                        'grn_item_id' => $grnItem->id,
                        'stock_batch_id' => $allocation['batch']->id,
                        'quantity_allocated' => $allocation['quantity'],
                    ]);
                }
            }

            return $grn;
        });

        return response()->json(['success' => true, 'id' => $grn->id]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_against_issue_note.approve_list', 'storage-supplies.grn-issue.approve', [
            'items' => GrnAgainstIssueNote::with(['issueNote.requisition.issuingSubdepartment', 'createdBy'])
                ->whereHas('issueNote.requisition', fn ($q) => $q->where('requesting_subdepartment_id', session('active_subdepartment_id')))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, GrnAgainstIssueNote $grn): JsonResponse
{
    abort_unless($grn->status === 'pending_approval', 403, 'Only pending GRNs can be approved.');
    abort_unless($grn->issueNote->requisition->requesting_subdepartment_id === session('active_subdepartment_id'), 403);

    $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
    $approver = User::where('email', $credentials['username'])->first();

    if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
        return response()->json(['message' => 'Invalid username or password.'], 422);
    }

    if (! $approver->hasApprovalPermission('grn_against_order_approval')) {
        return response()->json(['message' => 'This user is not authorized to approve this GRN.'], 403);
    }

    $grn->load('items.allocations.stockBatch');
    $issuingSubId = $grn->issueNote->requisition->issuing_subdepartment_id;
    $requestingSubId = $grn->issueNote->requisition->requesting_subdepartment_id;

    DB::transaction(function () use ($grn, $approver, $issuingSubId, $requestingSubId) {
        foreach ($grn->items as $grnItem) {
            foreach ($grnItem->allocations as $allocation) {
                $batch = $allocation->stockBatch()->lockForUpdate()->first();

                if ($batch->quantity_remaining < $allocation->quantity_allocated) {
                    abort(422, "Batch \"{$batch->batch_number}\" no longer has enough remaining stock to fulfill this GRN. Recreate the GRN to re-plan allocation.");
                }

                // Deduct from the issuing store's batch and balance.
                $batch->decrement('quantity_remaining', $allocation->quantity_allocated);

                $issuingBalance = ItemStockBalance::where('item_id', $grnItem->item_id)->where('subdepartment_id', $issuingSubId)->lockForUpdate()->first();
                $issuingNewBalance = $issuingBalance->quantity_balance - $allocation->quantity_allocated;
                $issuingBalance->update(['quantity_balance' => $issuingNewBalance]);

                StockLedger::create([
                    'item_id' => $grnItem->item_id,
                    'subdepartment_id' => $issuingSubId,
                    'movement_type' => 'issue',
                    'reference_type' => 'grn_against_issue_note',
                    'reference_id' => $grn->id,
                    'quantity_in' => 0,
                    'quantity_out' => $allocation->quantity_allocated,
                    'balance_after' => $issuingNewBalance,
                    'grn_batch_id' => null,
                    'created_by_user_id' => $approver->id,
                    'moved_at' => now(),
                ]);

                // Add to the requesting store — new batch row carrying the same identity forward.
                $newBatch = StockBatch::create([
                    'item_id' => $grnItem->item_id,
                    'subdepartment_id' => $requestingSubId,
                    'batch_number' => $batch->batch_number,
                    'manufacture_date' => $batch->manufacture_date,
                    'expiry_date' => $batch->expiry_date,
                    'buying_price' => $batch->buying_price,
                    'quantity_received' => $allocation->quantity_allocated,
                    'quantity_remaining' => $allocation->quantity_allocated,
                    'source_type' => 'grn_against_issue_note',
                    'source_id' => $grn->id,
                    'received_date' => now()->toDateString(),
                ]);

                $requestingBalance = ItemStockBalance::firstOrCreate(
                    ['item_id' => $grnItem->item_id, 'subdepartment_id' => $requestingSubId],
                    ['quantity_balance' => 0]
                );
                $requestingNewBalance = $requestingBalance->quantity_balance + $allocation->quantity_allocated;
                $requestingBalance->update(['quantity_balance' => $requestingNewBalance]);

                StockLedger::create([
                    'item_id' => $grnItem->item_id,
                    'subdepartment_id' => $requestingSubId,
                    'movement_type' => 'transfer_in',
                    'reference_type' => 'grn_against_issue_note',
                    'reference_id' => $grn->id,
                    'quantity_in' => $allocation->quantity_allocated,
                    'quantity_out' => 0,
                    'balance_after' => $requestingNewBalance,
                    'stock_batch_id' => $newBatch->id,
                    'created_by_user_id' => $approver->id,
                    'moved_at' => now(),
                ]);
            }
        }

        $grn->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);
    });

    return response()->json(['success' => true]);
}

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.grn_against_issue_note.previous_list', 'storage-supplies.grn-issue.previous', [
            'items' => GrnAgainstIssueNote::with(['issueNote.requisition.issuingSubdepartment', 'createdBy', 'approvedBy'])
                ->whereHas('issueNote.requisition', fn ($q) => $q->where('requesting_subdepartment_id', session('active_subdepartment_id')))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(GrnAgainstIssueNote $grn): View
    {
        $grn->load(['items.item.unitOfMeasure', 'items.allocations.stockBatch', 'issueNote.requisition.requestingSubdepartment.department.branch.company', 'createdBy', 'approvedBy']);

        $branch = $grn->issueNote->requisition->requestingSubdepartment?->department?->branch;

        return view('templates.storage_supplies.grn_against_issue_note.preview', [
            'grn' => $grn,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }
}
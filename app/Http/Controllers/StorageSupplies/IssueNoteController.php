<?php

namespace App\Http\Controllers\StorageSupplies;

use App\Http\Controllers\Controller;
use App\Models\IssueNote;
use App\Models\IssueNoteItem;
use App\Models\ItemStockBalance;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class IssueNoteController extends Controller
{
    public function newList(): View
    {
        return $this->nicePage('templates.storage_supplies.issue_note.new_list', 'storage-supplies.issue-note.new', [
            'items' => Requisition::with(['requestingSubdepartment', 'officer'])
                ->where('issuing_subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'approved')
                ->whereDoesntHave('issueNote')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(Requisition $requisition): View
    {
        abort_if($requisition->issueNote, 409, 'An Issue Note already exists for this Requisition.');
        abort_unless($requisition->issuing_subdepartment_id === session('active_subdepartment_id'), 403);
        abort_unless($requisition->status === 'approved', 403, 'Only approved requisitions can be issued.');

        $requisition->load('items.item.unitOfMeasure', 'requestingSubdepartment', 'officer');

        $balances = ItemStockBalance::where('subdepartment_id', session('active_subdepartment_id'))
            ->whereIn('item_id', $requisition->items->pluck('item_id'))
            ->pluck('quantity_balance', 'item_id');

        return $this->nicePage('templates.storage_supplies.issue_note.create', 'storage-supplies.issue-note.new', [
            'requisition' => $requisition,
            'balances' => $balances,
        ]);
    }

    public function store(Request $request, Requisition $requisition): JsonResponse
    {
        abort_if($requisition->issueNote, 409, 'An Issue Note already exists for this Requisition.');

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.requisition_item_id' => 'required|integer|exists:tbl_requisition_items,id',
            'items.*.quantity_issued' => 'required|integer|min:1',
        ]);

        $balances = ItemStockBalance::where('subdepartment_id', $requisition->issuing_subdepartment_id)
            ->pluck('quantity_balance', 'item_id');

        $issueNote = DB::transaction(function () use ($data, $requisition, $balances) {
            $issueNote = IssueNote::create([
                'requisition_id' => $requisition->id,
                'officer_user_id' => session('user_id'),
                'issue_date' => now()->toDateString(),
                'status' => 'pending_approval',
            ]);

            foreach ($data['items'] as $line) {
                $reqItem = $requisition->items()->findOrFail($line['requisition_item_id']);
                $available = $balances->get($reqItem->item_id, 0);

                if ($line['quantity_issued'] > $reqItem->quantity_requested) {
                    abort(422, "Quantity to issue for \"{$reqItem->item->product_name}\" cannot exceed the requested quantity ({$reqItem->quantity_requested}).");
                }

                if ($line['quantity_issued'] > $available) {
                    abort(422, "Quantity to issue for \"{$reqItem->item->product_name}\" ({$line['quantity_issued']}) exceeds current store balance ({$available}).");
                }

                IssueNoteItem::create([
                    'issue_note_id' => $issueNote->id,
                    'requisition_item_id' => $reqItem->id,
                    'item_id' => $reqItem->item_id,
                    'quantity_requested' => $reqItem->quantity_requested,
                    'quantity_issued' => $line['quantity_issued'],
                ]);
            }

            return $issueNote;
        });

        return response()->json(['success' => true, 'id' => $issueNote->id]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.storage_supplies.issue_note.approve_list', 'storage-supplies.issue-note.approve', [
            'items' => IssueNote::with(['requisition.requestingSubdepartment', 'officer'])
                ->whereHas('requisition', fn ($q) => $q->where('issuing_subdepartment_id', session('active_subdepartment_id')))
                ->where('status', 'pending_approval')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, IssueNote $issueNote): JsonResponse
    {
        abort_unless($issueNote->status === 'pending_approval', 403, 'Only pending Issue Notes can be approved.');
        abort_unless($issueNote->requisition->issuing_subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }

        if (! $approver->hasApprovalPermission('issue_note_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Issue Notes.'], 403);
        }

        // Authorizes only — stock actually moves at GRN Against Issue Note approval.
        $issueNote->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function previousList(): View
    {
        return $this->nicePage('templates.storage_supplies.issue_note.previous_list', 'storage-supplies.issue-note.previous', [
            'items' => IssueNote::with(['requisition.requestingSubdepartment', 'officer', 'approvedBy'])
                ->whereHas('requisition', fn ($q) => $q->where('issuing_subdepartment_id', session('active_subdepartment_id')))
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function preview(IssueNote $issueNote): View
    {
        $issueNote->load(['items.item.unitOfMeasure', 'requisition.requestingSubdepartment', 'requisition.issuingSubdepartment.department.branch.company', 'officer', 'approvedBy']);

        $branch = $issueNote->requisition->issuingSubdepartment?->department?->branch;

        return view('templates.storage_supplies.issue_note.preview', [
            'issueNote' => $issueNote,
            'branch' => $branch,
            'company' => $branch?->company,
        ]);
    }
}
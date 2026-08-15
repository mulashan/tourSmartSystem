<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\StockBatch;
use App\Models\StockLedger;
use App\Models\Subdepartment;
use App\Models\Workshop\Diagnosis;
use App\Models\Workshop\Invoice;
use App\Models\Workshop\JobCard;
use App\Models\Workshop\JobCompletion;
use App\Models\Workshop\JobMechanic;
use App\Models\Workshop\LabourEntry;
use App\Models\Workshop\Mechanic;
use App\Models\Workshop\PartUsed;
use App\Models\Workshop\QualityCheck;
use App\Models\Workshop\RepairOrder;
use App\Models\Workshop\Vehicle;
use App\Models\Workshop\VehicleInspection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Models\User;

class WorkshopController extends Controller
{
    public function dashboard(): View|RedirectResponse
    {
        return $this->nicePage('templates.workshop.dashboard.index', 'workshop.dashboard', [
            'counts' => [
                'new' => JobCard::where('status', 'new')->count(),
                'open' => JobCard::whereIn('status', ['new', 'assigned', 'in_progress', 'waiting_parts'])->count(),
                'completed' => JobCard::where('status', 'completed')->count(),
                'invoiced' => JobCard::where('status', 'invoiced')->count(),
                'closed' => JobCard::where('status', 'closed')->count(),
            ],
            'recentJobs' => JobCard::with(['customer', 'vehicle'])->latest()->limit(8)->get(),
            'labourTotal' => LabourEntry::sum('amount'),
            'partsTotal' => PartUsed::sum('total'),
            'invoiceTotal' => Invoice::sum('grand_total'),
        ]);
    }

    public function workflowManagement(): View|RedirectResponse
    {
        return redirect()->route('workshop.workflow-management.process', 'job-card');
    }

    public function workflowManagementProcess(string $process): View|RedirectResponse
    {
        $processCard = collect($this->workflowProcessCards())->firstWhere('slug', $process);

        abort_unless($processCard, 404);

        return $this->nicePage('templates.workshop.workflow_management.index', 'workshop.workflow.' . $process, [
            'processCards' => [$processCard],
            'currentProcess' => $processCard,
            'records' => $this->workflowProcessRecords($process),
            'pendingInspectionJobCards' => $process === 'vehicle-inspection'
                ? JobCard::with(['customer', 'vehicle'])->whereDoesntHave('vehicleInspections')->latest()->get()
                : collect(),
            'inspectedVehicleInspections' => $process === 'vehicle-inspection'
                ? VehicleInspection::with(['jobCard.customer', 'jobCard.vehicle'])->latest()->get()
                : collect(),
            'jobCards' => JobCard::with(['customer', 'vehicle'])->latest()->get(),
            'mechanics' => Mechanic::with('employee')->where('status', 'active')->orderBy('name')->get(),
            'parts' => Item::orderBy('product_name')->get(),
            'subdepartments' => Subdepartment::orderBy('Subdepartment_Name')->get(),
            'vehicles' => Vehicle::with('customer')->orderBy('registration_no')->get(),
        ]);
    }

    public function storeWorkflowProcess(Request $request, string $process): RedirectResponse
    {
        abort_unless(collect($this->workflowProcessCards())->contains('slug', $process), 404);

        return match ($process) {
            'job-card' => $this->storeWorkflowJobCard($request),
            'vehicle-inspection' => $this->storeWorkflowVehicleInspection($request),
            'diagnosis' => $this->storeWorkflowDiagnosis($request),
            'repair-maintenance' => $this->storeWorkflowRepairMaintenance($request),
            'spare-parts-usage' => $this->storeWorkflowSparePart($request),
            'labour-management' => $this->storeWorkflowLabour($request),
            'job-completion' => $this->storeWorkflowCompletion($request),
            'quality-check' => $this->storeWorkflowQualityCheck($request),
            'job-history' => back()->with('success', 'Job history is generated automatically from completed workflow records.'),
            default => abort(404),
        };
    }

    public function index(Request $request): View|RedirectResponse
    {
        $statusFilter = $request->query('status');
        $query = JobCard::with(['customer', 'vehicle', 'invoice'])->latest();

        if ($statusFilter === 'open') {
            $query->whereIn('status', ['new', 'assigned', 'in_progress', 'waiting_parts']);
        } elseif ($statusFilter && in_array($statusFilter, JobCard::STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        return $this->nicePage('templates.workshop.job_cards.index', 'workshop.job-cards', [
            'jobCards' => $query->get(),
            'vehicles' => Vehicle::with('customer')->orderBy('registration_no')->get(),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function storeJobCard(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'opened_date' => ['required', 'date'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'fuel_level' => ['nullable', 'string', 'max:40'],
            'reported_problems' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'expected_completion' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $jobCard = DB::transaction(function () use ($data) {
            $vehicle = Vehicle::findOrFail($data['vehicle_id']);

            return JobCard::create([
                'job_no' => $this->nextJobNo(),
                'customer_id' => $vehicle->customer_id,
                'vehicle_id' => $vehicle->id,
                'opened_by' => session('user_id'),
                'opened_date' => $data['opened_date'],
                'odometer_reading' => $data['odometer_reading'] ?? null,
                'fuel_level' => $data['fuel_level'] ?? null,
                'reported_problems' => $data['reported_problems'],
                'priority' => $data['priority'],
                'status' => 'new',
                'expected_completion' => $data['expected_completion'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);
        });

        return redirect()->route('workshop.job-cards.show', $jobCard)->with('success', 'Job card created successfully.');
    }

    private function storeWorkflowJobCard(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'opened_date' => ['required', 'date'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'reported_problems' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'maintenance_type' => ['nullable', Rule::in(['in_house', 'outside'])],
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        JobCard::create([
            'job_no' => $this->nextJobNo(),
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'opened_by' => session('user_id'),
            'opened_date' => $data['opened_date'],
            'odometer_reading' => $data['odometer_reading'] ?? null,
            'reported_problems' => $data['reported_problems'],
            'priority' => $data['priority'],
            'status' => 'new',
            'remarks' => 'Maintenance type: ' . str_replace('_', ' ', $data['maintenance_type'] ?? 'in_house'),
        ]);

        return back()->with('success', 'Job card saved successfully.');
    }

    private function storeWorkflowVehicleInspection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'inspection_date' => ['required', 'date'],
            'inspector_name' => ['nullable', 'string', 'max:255'],
            'fuel_level' => ['nullable', 'string', 'max:40'],
            'tyre_condition' => ['nullable', 'string', 'max:255'],
            'battery_condition' => ['nullable', 'string', 'max:255'],
            'fluid_status' => ['nullable', 'string', 'max:255'],
            'visible_damages' => ['nullable', 'string'],
            'safety_checklist' => ['nullable', 'string'],
            'initial_recommendation' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        VehicleInspection::create($data);

        return back()->with('success', 'Vehicle inspection saved successfully.');
    }

    private function storeWorkflowDiagnosis(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'mechanic_id' => ['nullable', 'exists:mechanics,id'],
            'findings' => ['required', 'string'],
            'root_cause' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'estimated_parts_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_labour_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'approved' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ]);

        Diagnosis::updateOrCreate(
            ['job_card_id' => $data['job_card_id']],
            [
                'mechanic_id' => $data['mechanic_id'] ?? null,
                'findings' => $data['findings'],
                'root_cause' => $data['root_cause'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'estimated_parts_cost' => $data['estimated_parts_cost'] ?? 0,
                'estimated_hours' => $data['estimated_hours'] ?? 0,
                'approved' => $request->boolean('approved'),
                'symptoms' => trim(($data['remarks'] ?? '') . ' Estimated labour cost: ' . ($data['estimated_labour_cost'] ?? 0)),
            ]
        );

        return back()->with('success', 'Diagnosis saved successfully.');
    }

    private function storeWorkflowRepairMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'maintenance_location' => ['required', Rule::in(['in_house', 'outside'])],
            'vendor_name' => ['nullable', 'required_if:maintenance_location,outside', 'string', 'max:255'],
            'external_cost' => ['nullable', 'required_if:maintenance_location,outside', 'numeric', 'min:0'],
            'repair_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'cancelled'])],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        RepairOrder::create([
            'job_card_id' => $data['job_card_id'],
            'repair_type' => $data['repair_type'],
            'description' => $data['description'] ?? null,
            'maintenance_location' => $data['maintenance_location'],
            'vendor_name' => $data['maintenance_location'] === 'outside' ? $data['vendor_name'] : null,
            'external_cost' => $data['maintenance_location'] === 'outside' ? ($data['external_cost'] ?? 0) : 0,
            'estimated_cost' => $data['maintenance_location'] === 'outside' ? ($data['external_cost'] ?? 0) : 0,
            'estimated_hours' => $data['estimated_hours'] ?? 0,
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Repair and maintenance record saved successfully.');
    }

    private function storeWorkflowSparePart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'part_id' => ['required', 'exists:tbl_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'subdepartment_id' => ['nullable', 'exists:tbl_subdepartment,Subdepartment_ID'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
        ]);

        PartUsed::create([
            'job_card_id' => $data['job_card_id'],
            'part_id' => $data['part_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total' => round($data['quantity'] * $data['unit_price'], 2),
            'issued_by' => session('user_id'),
            'issue_date' => $data['issue_date'],
            'subdepartment_id' => $data['subdepartment_id'] ?? null,
        ]);

        return back()->with('success', 'Spare part usage saved successfully.');
    }

    private function storeWorkflowLabour(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'mechanic_id' => ['required', 'exists:mechanics,id'],
            'work_done' => ['required', 'string'],
            'date' => ['required', 'date'],
            'hours' => ['required', 'numeric', 'min:0.01'],
            'rate' => ['required', 'numeric', 'min:0'],
        ]);

        LabourEntry::create($data + ['amount' => round($data['hours'] * $data['rate'], 2)]);

        return back()->with('success', 'Labour record saved successfully.');
    }

    private function storeWorkflowCompletion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'completed_date' => ['required', 'date'],
            'completion_notes' => ['nullable', 'string'],
            'vehicle_tested' => ['nullable', 'boolean'],
            'ready_for_inspection' => ['nullable', 'boolean'],
        ]);

        JobCompletion::updateOrCreate(
            ['job_card_id' => $data['job_card_id']],
            [
                'completion_notes' => $data['completion_notes'] ?? null,
                'completed_by' => session('user_id'),
                'completed_date' => $data['completed_date'],
                'vehicle_tested' => $request->boolean('vehicle_tested'),
                'ready_for_inspection' => $request->boolean('ready_for_inspection', true),
            ]
        );

        JobCard::whereKey($data['job_card_id'])->update(['status' => 'completed', 'completed_date' => $data['completed_date']]);

        return back()->with('success', 'Job completion saved successfully.');
    }

    private function storeWorkflowQualityCheck(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_card_id' => ['required', 'exists:job_cards,id'],
            'inspection_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['approved', 'returned_for_rework'])],
            'remarks' => ['nullable', 'string'],
        ]);

        foreach (['repair_completed', 'road_test', 'no_oil_leaks', 'brakes_checked', 'lights_working', 'complaint_resolved'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        QualityCheck::updateOrCreate(
            ['job_card_id' => $data['job_card_id']],
            $data + ['inspector_id' => session('user_id')]
        );

        return back()->with('success', 'Quality check saved successfully.');
    }

    public function show(JobCard $jobCard): View|RedirectResponse
    {
        $jobCard->load([
            'customer',
            'vehicle',
            'openedBy',
            'repairOrders',
            'diagnosis.mechanic',
            'mechanicAssignments.mechanic.employee',
            'labourEntries.mechanic',
            'partsUsed.part',
            'partsUsed.issuedBy',
            'completion',
            'qualityCheck',
            'invoice',
        ]);

        return $this->nicePage('templates.workshop.job_cards.show', 'workshop.job-cards', [
            'jobCard' => $jobCard,
            'workflowCards' => $this->workshopFlowCards(),
            'workflowAccess' => $this->workflowAccess($jobCard),
            'mechanics' => Mechanic::with('employee')->where('status', 'active')->orderBy('name')->get(),
            'employees' => Employee::orderBy('Employee_Name')->get(),
            'parts' => Item::orderBy('product_name')->get(),
            'subdepartments' => Subdepartment::orderBy('Subdepartment_Name')->get(),
        ]);
    }

    public function storeRepairOrder(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 2);

        $data = $request->validate([
            'repair_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'maintenance_location' => ['required', Rule::in(['in_house', 'outside'])],
            'vendor_name' => ['nullable', 'required_if:maintenance_location,outside', 'string', 'max:255'],
            'external_cost' => ['nullable', 'required_if:maintenance_location,outside', 'numeric', 'min:0'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'cancelled'])],
        ]);

        if ($data['maintenance_location'] === 'outside') {
            $data['external_cost'] = $data['external_cost'] ?? 0;
            $data['estimated_cost'] = $data['external_cost'];
        } else {
            $data['vendor_name'] = null;
            $data['external_cost'] = 0;
            $data['estimated_cost'] = 0;
        }

        $jobCard->repairOrders()->create($data);
        $this->advanceStatus($jobCard, 'in_progress');

        return back()->with('success', 'Repair order added.');
    }

    public function storeDiagnosis(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 3);

        $data = $request->validate([
            'mechanic_id' => ['nullable', 'exists:mechanics,id'],
            'symptoms' => ['nullable', 'string'],
            'findings' => ['required', 'string'],
            'root_cause' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'estimated_parts_cost' => ['nullable', 'numeric', 'min:0'],
            'approved' => ['nullable', 'boolean'],
        ]);

        $data['approved'] = $request->boolean('approved');
        Diagnosis::updateOrCreate(['job_card_id' => $jobCard->id], $data + ['job_card_id' => $jobCard->id]);
        $this->advanceStatus($jobCard, 'in_progress');

        return back()->with('success', 'Diagnosis saved.');
    }

    public function storeMechanic(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 4);

        $data = $request->validate([
            'mechanic_id' => ['nullable', 'exists:mechanics,id'],
            'employee_id' => ['nullable', 'required_without:mechanic_id', 'exists:tbl_employee,Employee_ID'],
            'name' => ['nullable', 'required_without_all:mechanic_id,employee_id', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'assigned_date' => ['required', 'date'],
            'role' => ['nullable', 'string', 'max:120'],
            'hours_worked' => ['nullable', 'numeric', 'min:0'],
            'completion_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['assigned', 'working', 'completed'])],
        ]);

        $mechanic = isset($data['mechanic_id'])
            ? Mechanic::findOrFail($data['mechanic_id'])
            : Mechanic::firstOrCreate(
                ['employee_id' => $data['employee_id'] ?? null, 'name' => $data['name'] ?? null],
                [
                    'specialization' => $data['specialization'] ?? null,
                    'hourly_rate' => $data['hourly_rate'] ?? 0,
                    'status' => 'active',
                ]
            );

        JobMechanic::updateOrCreate(
            ['job_card_id' => $jobCard->id, 'mechanic_id' => $mechanic->id],
            [
                'assigned_date' => $data['assigned_date'],
                'role' => $data['role'] ?? null,
                'hours_worked' => $data['hours_worked'] ?? 0,
                'completion_percent' => $data['completion_percent'] ?? 0,
                'status' => $data['status'],
            ]
        );

        $this->advanceStatus($jobCard, 'assigned');

        return back()->with('success', 'Mechanic assigned.');
    }

    public function storeLabour(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 5);

        $data = $request->validate([
            'mechanic_id' => ['required', 'exists:mechanics,id'],
            'work_done' => ['required', 'string'],
            'hours' => ['required', 'numeric', 'min:0.01'],
            'rate' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
        ]);

        $data['amount'] = round($data['hours'] * $data['rate'], 2);
        $jobCard->labourEntries()->create($data);
        $this->advanceStatus($jobCard, 'in_progress');

        return back()->with('success', 'Labour entry recorded.');
    }

    public function storePart(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 6);

        $data = $request->validate([
            'part_id' => ['required', 'exists:tbl_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'issue_date' => ['required', 'date'],
            'subdepartment_id' => ['required', 'exists:tbl_subdepartment,Subdepartment_ID'],
        ]);

        DB::transaction(function () use ($data, $jobCard) {
            $balance = ItemStockBalance::where('item_id', $data['part_id'])
                ->where('subdepartment_id', $data['subdepartment_id'])
                ->lockForUpdate()
                ->first();

            if (! $balance || $balance->quantity_balance < $data['quantity']) {
                abort(422, 'Insufficient stock balance for the selected part.');
            }

            $partUsed = PartUsed::create([
                'job_card_id' => $jobCard->id,
                'part_id' => $data['part_id'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'total' => round($data['quantity'] * $data['unit_price'], 2),
                'issued_by' => session('user_id'),
                'issue_date' => $data['issue_date'],
                'subdepartment_id' => $data['subdepartment_id'],
            ]);

            $batches = StockBatch::availableFefo($data['part_id'], $data['subdepartment_id'])
                ->lockForUpdate()
                ->get();

            if ($batches->isEmpty()) {
                $newBalance = $balance->quantity_balance - $data['quantity'];
                $balance->update(['quantity_balance' => $newBalance]);

                StockLedger::create([
                    'item_id' => $data['part_id'],
                    'subdepartment_id' => $data['subdepartment_id'],
                    'movement_type' => 'service_use',
                    'reference_type' => 'workshop_parts_used',
                    'reference_id' => $partUsed->id,
                    'quantity_in' => 0,
                    'quantity_out' => $data['quantity'],
                    'balance_after' => $newBalance,
                    'grn_batch_id' => null,
                    'stock_batch_id' => null,
                    'created_by_user_id' => session('user_id') ?: 1,
                    'moved_at' => now(),
                ]);

                return;
            }

            $remaining = $data['quantity'];
            $allocations = [];

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $quantityIssued = min($remaining, $batch->quantity_remaining);
                $allocations[] = ['batch' => $batch, 'quantity' => $quantityIssued];
                $remaining -= $quantityIssued;
            }

            if ($remaining > 0) {
                abort(422, "Insufficient batch stock for the selected part. {$remaining} unit(s) short.");
            }

            foreach ($allocations as $allocation) {
                $batch = $allocation['batch'];
                $quantityIssued = $allocation['quantity'];

                $batch->decrement('quantity_remaining', $quantityIssued);

                $newBalance = $balance->quantity_balance - $quantityIssued;
                $balance->update(['quantity_balance' => $newBalance]);
                $balance->quantity_balance = $newBalance;

                StockLedger::create([
                    'item_id' => $data['part_id'],
                    'subdepartment_id' => $data['subdepartment_id'],
                    'movement_type' => 'service_use',
                    'reference_type' => 'workshop_parts_used',
                    'reference_id' => $partUsed->id,
                    'quantity_in' => 0,
                    'quantity_out' => $quantityIssued,
                    'balance_after' => $newBalance,
                    'grn_batch_id' => null,
                    'stock_batch_id' => $batch->id,
                    'created_by_user_id' => session('user_id') ?: 1,
                    'moved_at' => now(),
                ]);
            }
        });

        $this->advanceStatus($jobCard, 'waiting_parts');

        return back()->with('success', 'Part issued and stock reduced.');
    }

    public function complete(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 7);

        $data = $request->validate([
            'completion_notes' => ['nullable', 'string'],
            'completed_date' => ['required', 'date'],
            'vehicle_tested' => ['nullable', 'boolean'],
            'ready_for_inspection' => ['nullable', 'boolean'],
        ]);

        JobCompletion::updateOrCreate(
            ['job_card_id' => $jobCard->id],
            [
                'completion_notes' => $data['completion_notes'] ?? null,
                'completed_by' => session('user_id'),
                'completed_date' => $data['completed_date'],
                'vehicle_tested' => $request->boolean('vehicle_tested'),
                'ready_for_inspection' => $request->boolean('ready_for_inspection', true),
            ]
        );

        $jobCard->update(['status' => 'completed', 'completed_date' => $data['completed_date']]);

        return back()->with('success', 'Repair marked complete.');
    }

    public function inspect(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 8);

        $data = $request->validate([
            'inspection_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['approved', 'returned_for_rework'])],
        ]);

        foreach (['repair_completed', 'road_test', 'no_oil_leaks', 'brakes_checked', 'lights_working', 'complaint_resolved'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        QualityCheck::updateOrCreate(
            ['job_card_id' => $jobCard->id],
            $data + ['job_card_id' => $jobCard->id, 'inspector_id' => session('user_id')]
        );

        $jobCard->update(['status' => $data['status'] === 'approved' ? 'completed' : 'in_progress']);

        return back()->with('success', 'Quality inspection saved.');
    }

    public function generateInvoice(Request $request, JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 9);

        if ($jobCard->invoice()->exists()) {
            return back()->withErrors('Invoice already generated for this job card.');
        }

        $data = $request->validate([
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'other_charges' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'issued', 'paid'])],
        ]);

        $labourTotal = $jobCard->labourEntries()->sum('amount');
        $partsTotal = $jobCard->partsUsed()->sum('total');
        $discount = $data['discount'] ?? 0;
        $otherCharges = $data['other_charges'] ?? 0;
        $tax = round((($labourTotal + $partsTotal + $otherCharges - $discount) * ($data['tax_rate'] ?? 18)) / 100, 2);

        Invoice::create([
            'job_card_id' => $jobCard->id,
            'invoice_no' => $this->nextInvoiceNo(),
            'labour_total' => $labourTotal,
            'parts_total' => $partsTotal,
            'tax' => $tax,
            'discount' => $discount,
            'other_charges' => $otherCharges,
            'grand_total' => round($labourTotal + $partsTotal + $otherCharges + $tax - $discount, 2),
            'status' => $data['status'],
        ]);

        $jobCard->update(['status' => 'invoiced']);

        return back()->with('success', 'Invoice generated.');
    }

    public function close(JobCard $jobCard): RedirectResponse
    {
        $this->requireWorkflowStep($jobCard, 10);

        abort_unless($jobCard->invoice, 422, 'Generate an invoice before closing the job card.');

        $jobCard->update(['status' => 'closed']);

        return redirect()->route('workshop.job-cards.index')->with('success', 'Job card closed.');
    }

    private function nextJobNo(): string
    {
        $next = ((int) JobCard::max('id')) + 1;

        return 'JC' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function nextInvoiceNo(): string
    {
        $next = ((int) Invoice::max('id')) + 1;

        return 'INV' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function requireWorkflowStep(JobCard $jobCard, int $step): void
    {
        $access = $this->workflowAccess($jobCard);

        abort_unless($access[$step]['unlocked'] ?? false, 422, $access[$step]['message'] ?? 'Complete the previous step first.');
    }

    private function workflowAccess(JobCard $jobCard): array
    {
        $hasRepairOrder = $jobCard->repairOrders()->exists();
        $hasDiagnosis = $jobCard->diagnosis()->exists();
        $hasMechanic = $jobCard->mechanicAssignments()->exists();
        $hasLabour = $jobCard->labourEntries()->exists();
        $hasParts = $jobCard->partsUsed()->exists();
        $hasCompletion = $jobCard->completion()->exists();
        $hasInspection = $jobCard->qualityCheck()->exists();
        $hasInvoice = $jobCard->invoice()->exists();

        $steps = [
            1 => ['unlocked' => true, 'message' => null],
            2 => ['unlocked' => true, 'message' => null],
            3 => ['unlocked' => $hasRepairOrder, 'message' => 'Add a repair order before diagnosis.'],
            4 => ['unlocked' => $hasDiagnosis, 'message' => 'Save diagnosis before assigning mechanics.'],
            5 => ['unlocked' => $hasMechanic, 'message' => 'Assign a mechanic before recording labour.'],
            6 => ['unlocked' => $hasLabour, 'message' => 'Record labour before issuing spare parts.'],
            7 => ['unlocked' => $hasParts, 'message' => 'Issue spare parts before completing repair.'],
            8 => ['unlocked' => $hasCompletion, 'message' => 'Complete repair before quality inspection.'],
            9 => ['unlocked' => $hasInspection, 'message' => 'Save quality inspection before generating invoice.'],
            10 => ['unlocked' => $hasInvoice, 'message' => 'Generate invoice before closing the job card.'],
        ];

        $permissionKeys = $this->workshopStepPermissionKeys();

        foreach ($steps as $step => $access) {
            $hasPermission = $this->canAccessWorkflowStep($step);
            $steps[$step]['visible'] = $hasPermission;

            if (! $hasPermission) {
                $label = $this->workshopFlowCards()->firstWhere('code', (string) $step)?->name ?? 'this workshop step';
                $steps[$step]['unlocked'] = false;
                $steps[$step]['message'] = isset($permissionKeys[$step])
                    ? "You do not have permission to access {$label}."
                    : null;
            }
        }

        return $steps;
    }

    private function workshopFlowCards()
    {
        return collect([
            'Create Job Card',
            'Repair Order',
            'Diagnosis',
            'Assign Mechanics',
            'Record Labour',
            'Issue Spare Parts',
            'Complete Repair',
            'Quality Inspection',
            'Generate Invoice',
            'Close Job Card',
        ])
            ->map(fn (string $name, int $index) => (object) [
                'name' => $name,
                'code' => (string) ($index + 1),
            ]);
    }

    private function workflowProcessSteps(): array
    {
        return [
            ['number' => 1, 'label' => 'Driver reports problem', 'icon' => 'bi-person', 'process' => 'job-card'],
            ['number' => 2, 'label' => 'Job card created', 'icon' => 'bi-file-earmark-text', 'process' => 'job-card'],
            ['number' => 3, 'label' => 'Vehicle inspection', 'icon' => 'bi-car-front', 'process' => 'vehicle-inspection'],
            ['number' => 4, 'label' => 'Diagnosis', 'icon' => 'bi-stethoscope', 'process' => 'diagnosis'],
            ['number' => 5, 'label' => 'Approval decision', 'icon' => 'bi-signpost-split', 'process' => 'repair-maintenance'],
            ['number' => 6, 'label' => 'Maintenance location', 'icon' => 'bi-building-gear', 'process' => 'repair-maintenance'],
            ['number' => 7, 'label' => 'Spare parts usage', 'icon' => 'bi-box-seam', 'process' => 'spare-parts-usage'],
            ['number' => 8, 'label' => 'Labour management', 'icon' => 'bi-person-gear', 'process' => 'labour-management'],
            ['number' => 9, 'label' => 'Quality check', 'icon' => 'bi-shield-check', 'process' => 'quality-check'],
            ['number' => 10, 'label' => 'Job completion', 'icon' => 'bi-check-circle', 'process' => 'job-completion'],
            ['number' => 11, 'label' => 'Job history', 'icon' => 'bi-folder2-open', 'process' => 'job-history'],
        ];
    }

    private function workflowProcessCards(): array
    {
        return [
            [
                'number' => 1,
                'slug' => 'job-card',
                'title' => 'Job Card',
                'icon' => 'bi-file-earmark-text',
                'purpose' => 'Start and register the maintenance case.',
                'steps' => ['Receive report from driver or transport officer', 'Select vehicle', 'Capture reported problem', 'Set priority and maintenance type', 'Generate job number'],
                'table_fields' => ['Job No', 'Date & Time', 'Vehicle', 'Driver Name', 'Odometer / Mileage', 'Problem Reported', 'Priority', 'Maintenance Type'],
                'form_fields' => [
                    ['name' => 'opened_date', 'label' => 'Date & Time', 'type' => 'date', 'required' => true],
                    ['name' => 'vehicle_id', 'label' => 'Vehicle', 'type' => 'vehicle', 'required' => true],
                    ['name' => 'driver_name', 'label' => 'Driver Name', 'type' => 'text'],
                    ['name' => 'odometer_reading', 'label' => 'Odometer / Mileage', 'type' => 'number'],
                    ['name' => 'reported_problems', 'label' => 'Problem Reported', 'type' => 'textarea', 'required' => true],
                    ['name' => 'priority', 'label' => 'Priority', 'type' => 'select', 'required' => true, 'options' => ['normal' => 'Normal', 'low' => 'Low', 'high' => 'High', 'urgent' => 'Urgent']],
                    ['name' => 'maintenance_type', 'label' => 'Maintenance Type', 'type' => 'select', 'options' => ['in_house' => 'In-house Workshop', 'outside' => 'Outside Workshop / Vendor']],
                ],
            ],
            [
                'number' => 2,
                'slug' => 'vehicle-inspection',
                'title' => 'Vehicle Inspection',
                'icon' => 'bi-car-front',
                'purpose' => 'Record initial condition before diagnosis or repair.',
                'steps' => ['Inspect body, engine, tyres, battery and fluids', 'Note visible damage or safety issues', 'Mark vehicle status', 'Submit for diagnosis'],
                'table_fields' => ['Inspection No', 'Job No', 'Inspection Date', 'Inspector Name', 'Fuel Level', 'Tyre Condition', 'Battery Condition', 'Fluid Status', 'Visible Damages', 'Safety Checklist', 'Initial Recommendation', 'Remarks'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'inspection_date', 'label' => 'Inspection Date', 'type' => 'date', 'required' => true],
                    ['name' => 'inspector_name', 'label' => 'Inspector Name', 'type' => 'text'],
                    ['name' => 'fuel_level', 'label' => 'Fuel Level', 'type' => 'text'],
                    ['name' => 'tyre_condition', 'label' => 'Tyre Condition', 'type' => 'text'],
                    ['name' => 'battery_condition', 'label' => 'Battery Condition', 'type' => 'text'],
                    ['name' => 'fluid_status', 'label' => 'Oil / Coolant / Brake Fluid Status', 'type' => 'text'],
                    ['name' => 'visible_damages', 'label' => 'Visible Damages', 'type' => 'textarea'],
                    ['name' => 'safety_checklist', 'label' => 'Safety Checklist', 'type' => 'textarea'],
                    ['name' => 'initial_recommendation', 'label' => 'Initial Recommendation', 'type' => 'textarea'],
                    ['name' => 'remarks', 'label' => 'Inspection Remarks', 'type' => 'textarea'],
                ],
            ],
            [
                'number' => 3,
                'slug' => 'diagnosis',
                'title' => 'Diagnosis',
                'icon' => 'bi-stethoscope',
                'purpose' => 'Confirm the actual fault and recommend action.',
                'steps' => ['Technician tests vehicle', 'Identify root cause', 'Record findings', 'Estimate cost and time', 'Recommend repair and submit for approval'],
                'table_fields' => ['Diagnosis No', 'Job No', 'Technician Name', 'Fault Found', 'Root Cause', 'Recommended Action', 'Estimated Parts Cost', 'Estimated Labour Cost', 'Estimated Total Cost', 'Estimated Repair Time', 'Approval Status', 'Diagnosis Remarks'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'mechanic_id', 'label' => 'Technician Name', 'type' => 'mechanic'],
                    ['name' => 'findings', 'label' => 'Fault Found', 'type' => 'textarea', 'required' => true],
                    ['name' => 'root_cause', 'label' => 'Root Cause', 'type' => 'textarea'],
                    ['name' => 'recommendation', 'label' => 'Recommended Action', 'type' => 'textarea'],
                    ['name' => 'estimated_parts_cost', 'label' => 'Estimated Parts Cost', 'type' => 'number'],
                    ['name' => 'estimated_labour_cost', 'label' => 'Estimated Labour Cost', 'type' => 'number'],
                    ['name' => 'estimated_hours', 'label' => 'Estimated Repair Time', 'type' => 'number'],
                    ['name' => 'approved', 'label' => 'Approval Status', 'type' => 'checkbox'],
                    ['name' => 'remarks', 'label' => 'Diagnosis Remarks', 'type' => 'textarea'],
                ],
            ],
            [
                'number' => 4,
                'slug' => 'repair-maintenance',
                'title' => 'Repair & Maintenance',
                'icon' => 'bi-tools',
                'purpose' => 'Execute the approved repair work.',
                'steps' => ['Approve maintenance', 'Assign workshop or outside vendor', 'Assign technician', 'Perform repair tasks', 'Update progress'],
                'table_fields' => ['Maintenance No', 'Job No', 'Maintenance Location', 'Vendor Name', 'External Workshop Cost', 'Repair Tasks', 'Work Status', 'Progress Notes'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'maintenance_location', 'label' => 'Maintenance Location', 'type' => 'maintenance_location', 'required' => true],
                    ['name' => 'vendor_name', 'label' => 'Vendor Name', 'type' => 'text', 'external_only' => true],
                    ['name' => 'external_cost', 'label' => 'External Workshop Cost', 'type' => 'number', 'external_only' => true],
                    ['name' => 'repair_type', 'label' => 'Repair Tasks', 'type' => 'text', 'required' => true],
                    ['name' => 'estimated_hours', 'label' => 'Estimated Hours', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Work Status', 'type' => 'select', 'required' => true, 'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled']],
                    ['name' => 'description', 'label' => 'Progress Notes', 'type' => 'textarea'],
                ],
            ],
            [
                'number' => 5,
                'slug' => 'spare-parts-usage',
                'title' => 'Spare Parts Usage',
                'icon' => 'bi-box-seam',
                'purpose' => 'Control issued spare parts and cost.',
                'steps' => ['Request required parts', 'Check store availability', 'Issue or purchase parts', 'Link parts to job card', 'Record quantity and cost'],
                'table_fields' => ['Parts Usage No', 'Job No', 'Part Name', 'Part Code', 'Quantity Issued', 'Unit Cost', 'Total Cost', 'Store / Source', 'Issued By', 'Usage Date'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'part_id', 'label' => 'Part Name', 'type' => 'part', 'required' => true],
                    ['name' => 'quantity', 'label' => 'Quantity Issued', 'type' => 'number', 'required' => true],
                    ['name' => 'unit_price', 'label' => 'Unit Cost', 'type' => 'number', 'required' => true],
                    ['name' => 'subdepartment_id', 'label' => 'Store / Source', 'type' => 'subdepartment'],
                    ['name' => 'vendor_name', 'label' => 'Vendor', 'type' => 'text'],
                    ['name' => 'issue_date', 'label' => 'Usage Date', 'type' => 'date', 'required' => true],
                ],
            ],
            [
                'number' => 6,
                'slug' => 'labour-management',
                'title' => 'Labour Management',
                'icon' => 'bi-person-gear',
                'purpose' => 'Track technicians, hours and labour charges.',
                'steps' => ['Assign labour resources', 'Capture work hours per task', 'Record labour cost', 'Monitor task completion'],
                'table_fields' => ['Labour Entry No', 'Job No', 'Technician Name', 'Task Name', 'Date Worked', 'Hours Spent', 'Hourly Rate', 'Labour Cost'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'mechanic_id', 'label' => 'Technician Name', 'type' => 'mechanic', 'required' => true],
                    ['name' => 'work_done', 'label' => 'Task Name', 'type' => 'textarea', 'required' => true],
                    ['name' => 'date', 'label' => 'Date Worked', 'type' => 'date', 'required' => true],
                    ['name' => 'hours', 'label' => 'Hours Spent', 'type' => 'number', 'required' => true],
                    ['name' => 'rate', 'label' => 'Hourly Rate', 'type' => 'number', 'required' => true],
                ],
            ],
            [
                'number' => 7,
                'slug' => 'job-completion',
                'title' => 'Job Completion',
                'icon' => 'bi-check-circle',
                'purpose' => 'Confirm work is finished and ready for handover.',
                'steps' => ['Review completed work', 'Summarize parts and labour costs', 'Record final status', 'Prepare handover'],
                'table_fields' => ['Completion No', 'Job No', 'Completion Date', 'Final Work Summary', 'Total Parts Cost', 'Total Labour Cost', 'Total Maintenance Cost', 'Vehicle Tested', 'Ready for Inspection'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'completed_date', 'label' => 'Completion Date', 'type' => 'date', 'required' => true],
                    ['name' => 'completion_notes', 'label' => 'Final Work Summary', 'type' => 'textarea'],
                    ['name' => 'vehicle_tested', 'label' => 'Vehicle Tested', 'type' => 'checkbox'],
                    ['name' => 'ready_for_inspection', 'label' => 'Ready for Inspection', 'type' => 'checkbox'],
                ],
            ],
            [
                'number' => 8,
                'slug' => 'quality-check',
                'title' => 'Quality Check',
                'icon' => 'bi-shield-check',
                'purpose' => 'Verify the vehicle is repaired correctly and safe to use.',
                'steps' => ['Inspect repaired areas', 'Test drive if required', 'Confirm quality and safety', 'Approve or return for rework'],
                'table_fields' => ['Quality Check No', 'Job No', 'Check Date', 'Checklist Result', 'Road Test Result', 'Safety Status', 'Pass / Fail', 'Rework Needed?', 'Quality Remarks'],
                'form_fields' => [
                    ['name' => 'job_card_id', 'label' => 'Job No', 'type' => 'job_card', 'required' => true],
                    ['name' => 'inspection_date', 'label' => 'Check Date', 'type' => 'date', 'required' => true],
                    ['name' => 'repair_completed', 'label' => 'Checklist Result', 'type' => 'checkbox'],
                    ['name' => 'road_test', 'label' => 'Road Test Result', 'type' => 'checkbox'],
                    ['name' => 'complaint_resolved', 'label' => 'Safety Status', 'type' => 'checkbox'],
                    ['name' => 'status', 'label' => 'Pass / Fail', 'type' => 'select', 'required' => true, 'options' => ['approved' => 'Pass', 'returned_for_rework' => 'Fail / Return for Rework']],
                    ['name' => 'remarks', 'label' => 'Quality Remarks', 'type' => 'textarea'],
                ],
            ],
            [
                'number' => 9,
                'slug' => 'job-history',
                'title' => 'Job History',
                'icon' => 'bi-folder2-open',
                'purpose' => 'Keep a full maintenance record for future reference.',
                'steps' => ['Save all stages automatically', 'View past repairs by vehicle', 'Filter by date, status, vendor, or workshop', 'Print or export reports'],
                'table_fields' => ['Job No', 'Vehicle No', 'Maintenance Location', 'Diagnosis Status', 'Completion Status', 'Vendor / Workshop', 'Total Cost', 'Completed Date'],
                'form_fields' => [],
            ],
        ];
    }

    private function workflowProcessRecords(string $process): array
    {
        return match ($process) {
            'job-card' => JobCard::with(['vehicle', 'customer'])->latest()->get()->map(fn (JobCard $jobCard): array => [
                $jobCard->job_no,
                optional($jobCard->opened_date)->format('d M Y'),
                $jobCard->vehicle->registration_no ?? '-',
                $jobCard->customer->name ?? '-',
                $jobCard->odometer_reading ? number_format($jobCard->odometer_reading) : '-',
                $jobCard->reported_problems ?: '-',
                ucfirst($jobCard->priority),
                str_contains((string) $jobCard->remarks, 'outside') ? 'Outside Workshop' : 'In-house Workshop',
            ])->all(),

            'vehicle-inspection' => VehicleInspection::with('jobCard.vehicle')->latest()->get()->map(fn (VehicleInspection $inspection): array => [
                'VI' . str_pad((string) $inspection->id, 5, '0', STR_PAD_LEFT),
                $inspection->jobCard->job_no ?? '-',
                optional($inspection->inspection_date)->format('d M Y'),
                $inspection->inspector_name ?: '-',
                $inspection->fuel_level ?: '-',
                $inspection->tyre_condition ?: '-',
                $inspection->battery_condition ?: '-',
                $inspection->fluid_status ?: '-',
                $inspection->visible_damages ?: '-',
                $inspection->safety_checklist ?: '-',
                $inspection->initial_recommendation ?: '-',
                $inspection->remarks ?: '-',
            ])->all(),

            'diagnosis' => Diagnosis::with(['mechanic', 'jobCard'])->latest()->get()->map(fn (Diagnosis $diagnosis): array => [
                'DG' . str_pad((string) $diagnosis->id, 5, '0', STR_PAD_LEFT),
                $diagnosis->jobCard->job_no ?? '-',
                $diagnosis->mechanic->display_name ?? '-',
                $diagnosis->findings ?: '-',
                $diagnosis->root_cause ?: '-',
                $diagnosis->recommendation ?: '-',
                number_format($diagnosis->estimated_parts_cost, 2),
                '-',
                number_format($diagnosis->estimated_parts_cost, 2),
                $diagnosis->estimated_hours,
                $diagnosis->approved ? 'Approved' : 'Pending',
                $diagnosis->symptoms ?: '-',
            ])->all(),

            'repair-maintenance' => RepairOrder::with('jobCard')->latest()->get()->map(fn (RepairOrder $order): array => [
                'MT' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
                $order->jobCard->job_no ?? '-',
                ($order->maintenance_location ?? 'in_house') === 'outside' ? 'Outside Workshop' : 'In-house Workshop',
                $order->vendor_name ?: '-',
                ($order->maintenance_location ?? 'in_house') === 'outside' ? number_format($order->external_cost, 2) : '-',
                $order->repair_type,
                ucfirst(str_replace('_', ' ', $order->status)),
                $order->description ?: '-',
            ])->all(),

            'spare-parts-usage' => PartUsed::with(['jobCard', 'part', 'subdepartment', 'issuedBy'])->latest()->get()->map(fn (PartUsed $part): array => [
                'PU' . str_pad((string) $part->id, 5, '0', STR_PAD_LEFT),
                $part->jobCard->job_no ?? '-',
                $part->part->product_name ?? '-',
                $part->part->product_code ?? '-',
                $part->quantity,
                number_format($part->unit_price, 2),
                number_format($part->total, 2),
                $part->subdepartment->Subdepartment_Name ?? '-',
                $part->issuedBy->name ?? '-',
                optional($part->issue_date)->format('d M Y'),
            ])->all(),

            'labour-management' => LabourEntry::with(['jobCard', 'mechanic'])->latest()->get()->map(fn (LabourEntry $entry): array => [
                'LB' . str_pad((string) $entry->id, 5, '0', STR_PAD_LEFT),
                $entry->jobCard->job_no ?? '-',
                $entry->mechanic->display_name ?? '-',
                $entry->work_done,
                optional($entry->date)->format('d M Y'),
                $entry->hours,
                number_format($entry->rate, 2),
                number_format($entry->amount, 2),
            ])->all(),

            'job-completion' => JobCompletion::with(['jobCard.partsUsed', 'jobCard.labourEntries'])->latest()->get()->map(function (JobCompletion $completion): array {
                $partsTotal = $completion->jobCard?->partsUsed->sum('total') ?? 0;
                $labourTotal = $completion->jobCard?->labourEntries->sum('amount') ?? 0;

                return [
                    'CM' . str_pad((string) $completion->id, 5, '0', STR_PAD_LEFT),
                    $completion->jobCard->job_no ?? '-',
                    optional($completion->completed_date)->format('d M Y'),
                    $completion->completion_notes ?: '-',
                    number_format($partsTotal, 2),
                    number_format($labourTotal, 2),
                    number_format($partsTotal + $labourTotal, 2),
                    $completion->vehicle_tested ? 'Yes' : 'No',
                    $completion->ready_for_inspection ? 'Yes' : 'No',
                ];
            })->all(),

            'quality-check' => QualityCheck::with('jobCard')->latest()->get()->map(fn (QualityCheck $check): array => [
                'QC' . str_pad((string) $check->id, 5, '0', STR_PAD_LEFT),
                $check->jobCard->job_no ?? '-',
                optional($check->inspection_date)->format('d M Y'),
                $check->repair_completed ? 'Passed' : 'Pending',
                $check->road_test ? 'Passed' : 'Not done',
                $check->complaint_resolved ? 'Safe' : 'Needs review',
                $check->status === 'approved' ? 'Pass' : 'Fail',
                $check->status === 'returned_for_rework' ? 'Yes' : 'No',
                $check->remarks ?: '-',
            ])->all(),

            'job-history' => JobCard::with(['vehicle', 'diagnosis', 'completion', 'repairOrders', 'partsUsed', 'labourEntries'])->latest()->get()->map(function (JobCard $jobCard): array {
                $partsTotal = $jobCard->partsUsed->sum('total');
                $labourTotal = $jobCard->labourEntries->sum('amount');
                $outsideOrder = $jobCard->repairOrders->firstWhere('maintenance_location', 'outside');

                return [
                    $jobCard->job_no,
                    $jobCard->vehicle->registration_no ?? '-',
                    $outsideOrder ? 'Outside Workshop' : 'In-house Workshop',
                    $jobCard->diagnosis ? ($jobCard->diagnosis->approved ? 'Approved' : 'Pending') : 'Pending',
                    $jobCard->completion ? 'Completed' : ucfirst(str_replace('_', ' ', $jobCard->status)),
                    $outsideOrder->vendor_name ?? '-',
                    number_format($partsTotal + $labourTotal + ($outsideOrder->external_cost ?? 0), 2),
                    optional($jobCard->completed_date)->format('d M Y') ?: '-',
                ];
            })->all(),

            default => [],
        };
    }

    private function workshopStepPermissionKeys(): array
    {
        return [
            2 => 'repair_order',
            3 => 'diagnosis',
            4 => 'assign_mechanics',
            5 => 'record_labour',
            6 => 'issue_spare_parts',
            7 => 'complete_repair',
            8 => 'quality_inspection',
            9 => 'generate_invoice',
            10 => 'close_job_card',
        ];
    }

    private function canAccessWorkflowStep(int $step): bool
    {
        if ($step === 1) {
            return true;
        }

        $permissionKey = $this->workshopStepPermissionKeys()[$step] ?? null;

        if (! $permissionKey || ! session('user_id')) {
            return false;
        }

        return User::query()
            ->whereKey(session('user_id'))
            ->whereHas('workshopPermissions', fn ($query) => $query->where('permission_key', $permissionKey))
            ->exists();
    }

    private function advanceStatus(JobCard $jobCard, string $status): void
    {
        if (in_array($jobCard->status, ['new', 'assigned', 'in_progress', 'waiting_parts'], true)) {
            $jobCard->update(['status' => $status]);
        }
    }
}

<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemStockBalance;
use App\Models\StockLedger;
use App\Models\Subdepartment;
use App\Models\Workshop\Customer;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            'customers' => Customer::orderBy('name')->get(),
            'vehicles' => Vehicle::with('customer')->orderBy('registration_no')->get(),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function storeJobCard(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],
            'customer_id' => ['nullable', 'required_if:customer_mode,existing', 'exists:customers,id'],
            'customer_name' => ['nullable', 'required_if:customer_mode,new', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:80'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'vehicle_mode' => ['required', Rule::in(['existing', 'new'])],
            'vehicle_id' => ['nullable', 'required_if:vehicle_mode,existing', 'exists:vehicles,id'],
            'registration_no' => ['nullable', 'required_if:vehicle_mode,new', 'string', 'max:80'],
            'make' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:80'],
            'vin' => ['nullable', 'string', 'max:120'],
            'opened_date' => ['required', 'date'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'fuel_level' => ['nullable', 'string', 'max:40'],
            'reported_problems' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'expected_completion' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $jobCard = DB::transaction(function () use ($data) {
            $customer = $data['customer_mode'] === 'new'
                ? Customer::create([
                    'name' => $data['customer_name'],
                    'phone' => $data['customer_phone'] ?? null,
                    'email' => $data['customer_email'] ?? null,
                    'address' => $data['customer_address'] ?? null,
                ])
                : Customer::findOrFail($data['customer_id']);

            $vehicle = $data['vehicle_mode'] === 'new'
                ? Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_no' => $data['registration_no'],
                    'make' => $data['make'] ?? null,
                    'model' => $data['model'] ?? null,
                    'color' => $data['color'] ?? null,
                    'vin' => $data['vin'] ?? null,
                ])
                : Vehicle::findOrFail($data['vehicle_id']);

            return JobCard::create([
                'job_no' => $this->nextJobNo(),
                'customer_id' => $customer->id,
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
            'mechanics' => Mechanic::with('employee')->where('status', 'active')->orderBy('name')->get(),
            'employees' => Employee::orderBy('Employee_Name')->get(),
            'parts' => Item::orderBy('product_name')->get(),
            'subdepartments' => Subdepartment::orderBy('Subdepartment_Name')->get(),
        ]);
    }

    public function storeRepairOrder(Request $request, JobCard $jobCard): RedirectResponse
    {
        $data = $request->validate([
            'repair_type' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['open', 'in_progress', 'completed', 'cancelled'])],
        ]);

        $jobCard->repairOrders()->create($data);
        $this->advanceStatus($jobCard, 'in_progress');

        return back()->with('success', 'Repair order added.');
    }

    public function storeDiagnosis(Request $request, JobCard $jobCard): RedirectResponse
    {
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

            $newBalance = $balance->quantity_balance - $data['quantity'];
            $balance->update(['quantity_balance' => $newBalance]);

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

            StockLedger::create([
                'item_id' => $data['part_id'],
                'subdepartment_id' => $data['subdepartment_id'],
                'movement_type' => 'issue',
                'reference_type' => 'workshop_parts_used',
                'reference_id' => $partUsed->id,
                'quantity_in' => 0,
                'quantity_out' => $data['quantity'],
                'balance_after' => $newBalance,
                'grn_batch_id' => null,
                'created_by_user_id' => session('user_id') ?: 1,
                'moved_at' => now(),
            ]);
        });

        $this->advanceStatus($jobCard, 'waiting_parts');

        return back()->with('success', 'Part issued and stock reduced.');
    }

    public function complete(Request $request, JobCard $jobCard): RedirectResponse
    {
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

    private function advanceStatus(JobCard $jobCard, string $status): void
    {
        if (in_array($jobCard->status, ['new', 'assigned', 'in_progress', 'waiting_parts'], true)) {
            $jobCard->update(['status' => $status]);
        }
    }
}

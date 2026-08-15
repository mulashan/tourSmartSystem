<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetIncident;
use App\Models\FuelOpenOrderItem;
use App\Models\Itinerary;
use App\Models\ItineraryFuel;
use App\Models\MaintenanceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FleetReportController extends Controller
{
    private function eligibleSubdepartments()
    {
        $user = User::with('subdepartments.department.departmentNature')->find(session('user_id'));

        return ($user?->subdepartments ?? collect())
            ->filter(fn ($sub) => optional($sub->department?->departmentNature)->department_nature === 'Fleet Management')
            ->sortBy('Subdepartment_Name')->values();
    }

    private function assertEligibleSubdepartment(int $id): void
    {
        abort_unless($this->eligibleSubdepartments()->contains('Subdepartment_ID', $id), Response::HTTP_FORBIDDEN);
    }

    public function vehicleUtilization(): View
    {
        return $this->nicePage('templates.fleet.reports.vehicle_utilization', 'fleet.reports.vehicle-utilization', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function vehicleUtilizationData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = Vehicle::where('subdepartment_id', $subId)->get()->map(function ($v) use ($startDate, $endDate) {
            $trips = Itinerary::where('vehicle_id', $v->id)->whereIn('status', ['completed', 'closed'])
                ->whereBetween('start_date', [$startDate, $endDate])->get();

            return [
                'vehicle' => $v,
                'trip_count' => $trips->count(),
                'days_used' => $trips->sum(fn ($t) => \Carbon\Carbon::parse($t->start_date)->diffInDays($t->end_date) + 1),
            ];
        });

        return view('templates.fleet.reports.partials.vehicle_utilization_table', compact('rows'));
    }

    public function fuelConsumption(): View
    {
        return $this->nicePage('templates.fleet.reports.fuel_consumption', 'fleet.reports.fuel-consumption', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function fuelConsumptionData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $tripFuel = ItineraryFuel::with('vehicle')->where('status', 'issued')
            ->whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subId))
            ->whereBetween('issued_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])->get();

        $openOrderFuel = FuelOpenOrderItem::with('vehicle')
            ->whereHas('openOrder', fn ($q) => $q->where('subdepartment_id', $subId))
            ->whereBetween('recorded_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])->get();

        $rows = $tripFuel->map(fn ($f) => ['vehicle' => $f->vehicle, 'source' => 'Itinerary Fuel', 'quantity' => $f->issued_quantity, 'amount' => $f->issued_quantity * $f->unit_price])
            ->concat($openOrderFuel->map(fn ($i) => ['vehicle' => $i->vehicle, 'source' => 'Open Order', 'quantity' => $i->quantity, 'amount' => $i->total_amount]))
            ->groupBy(fn ($r) => $r['vehicle']->registration_no ?? 'Unknown')
            ->map(fn ($group, $reg) => ['vehicle' => $reg, 'total_quantity' => $group->sum('quantity'), 'total_amount' => $group->sum('amount')])
            ->values();

        return view('templates.fleet.reports.partials.fuel_consumption_table', compact('rows'));
    }

    public function tripHistory(): View
    {
        return $this->nicePage('templates.fleet.reports.trip_history', 'fleet.reports.trip-history', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function tripHistoryData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = Itinerary::with(['vehicle', 'driver'])->where('subdepartment_id', $subId)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')->get();

        return view('templates.fleet.reports.partials.trip_history_table', compact('rows'));
    }

    public function insuranceExpiry(): View
    {
        return $this->nicePage('templates.fleet.reports.insurance_expiry', 'fleet.reports.insurance-expiry', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function insuranceExpiryData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $withinDays = (int) $request->query('within_days', 60);

        $rows = VehicleInsurance::with(['vehicle', 'insuranceType'])
            ->whereHas('vehicle', fn ($q) => $q->where('subdepartment_id', $subId))
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('tbl_vehicle_insurance')->groupBy('vehicle_id');
            })
            ->where('expire_date', '<=', now()->addDays($withinDays)->toDateString())
            ->orderBy('expire_date')->get();

        return view('templates.fleet.reports.partials.insurance_expiry_table', compact('rows'));
    }

    public function maintenanceHistory(): View
    {
        return $this->nicePage('templates.fleet.reports.maintenance_history', 'fleet.reports.maintenance-history', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function maintenanceHistoryData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = MaintenanceOrder::with(['vehicle', 'workshop'])
            ->whereHas('vehicle', fn ($q) => $q->where('subdepartment_id', $subId))
            ->whereBetween('created_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->orderByDesc('id')->get();

        return view('templates.fleet.reports.partials.maintenance_history_table', compact('rows'));
    }

    public function incidentsReport(): View
    {
        return $this->nicePage('templates.fleet.reports.incidents_report', 'fleet.reports.incidents', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function incidentsReportData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = FleetIncident::with(['vehicle', 'driver'])->where('subdepartment_id', $subId)
            ->whereBetween('incident_date', [$startDate, $endDate])
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->orderByDesc('id')->get();

        $totalCost = $rows->sum(fn ($r) => $r->actual_cost ?? $r->estimated_cost ?? 0);

        return view('templates.fleet.reports.partials.incidents_report_table', compact('rows', 'totalCost'));
    }
}
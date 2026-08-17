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

    public function fuelByStation(): View
    {
        return $this->nicePage('templates.fleet.reports.fuel_by_station', 'fleet.reports.fuel-by-station', [
            'subdepartments' => $this->eligibleSubdepartments(),
            'fuelSources' => \App\Models\Lookup::ofType('fuel_source')->orderBy('name')->get(),
        ]);
    }

    public function fuelByStationData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $type = $request->query('type', 'itinerary_leg');
        $fuelSourceId = $request->query('fuel_source_id');

        if ($type === 'open_order') {
            $orders = \App\Models\FuelOpenOrder::with(['fuelSource', 'items'])
                ->where('subdepartment_id', $subId)
                ->when($fuelSourceId, fn ($q, $s) => $q->where('fuel_source_id', $s))
                ->whereBetween('opened_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                ->orderByDesc('opened_at')
                ->get()
                ->map(function ($order) {
                    $order->total_quantity = $order->items->sum('quantity');
                    $order->total_amount = $order->items->sum('total_amount');
                    return $order;
                });

            return view('templates.fleet.reports.partials.fuel_by_station_open_order_table', compact('orders'));
        }

        $rows = ItineraryFuel::with(['vehicle', 'driver', 'fuelSource'])
            ->where('status', 'issued')
            ->whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subId))
            ->when($fuelSourceId, fn ($q, $s) => $q->where('fuel_source_id', $s))
            ->whereBetween('issued_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->orderBy('issued_at')->get();

        $totalQty = $rows->sum('issued_quantity');
        $totalCost = $rows->sum(fn ($r) => $r->issued_quantity * $r->unit_price);

        return view('templates.fleet.reports.partials.fuel_by_station_trip_table', compact('rows', 'totalQty', 'totalCost'));
    }

    // ---------- Cost per Kilometer ----------

    public function costPerKm(): View
    {
        return $this->nicePage('templates.fleet.reports.cost_per_km', 'fleet.reports.cost-per-km', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function costPerKmData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = Vehicle::where('subdepartment_id', $subId)->get()->map(function ($v) use ($startDate, $endDate) {
            $trips = Itinerary::with('gatePass')->where('vehicle_id', $v->id)
                ->where('status', 'closed')->whereBetween('start_date', [$startDate, $endDate])->get();

            $distance = $trips->sum(function ($t) {
                $startOdo = $t->gatePass->odometer_reading ?? null;
                return ($startOdo !== null && $t->return_odometer !== null) ? max(0, $t->return_odometer - $startOdo) : 0;
            });

            $tripFuelCost = ItineraryFuel::where('vehicle_id', $v->id)->where('status', 'issued')
                ->whereBetween('issued_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                ->get()->sum(fn ($f) => $f->issued_quantity * $f->unit_price);

            $openOrderFuelCost = FuelOpenOrderItem::where('vehicle_id', $v->id)
                ->whereBetween('recorded_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                ->sum('total_amount');

            $totalCost = $tripFuelCost + $openOrderFuelCost;

            return [
                'vehicle' => $v, 'distance' => $distance, 'total_cost' => $totalCost,
                'cost_per_km' => $distance > 0 ? round($totalCost / $distance, 2) : null,
            ];
        })->sortByDesc(fn ($r) => $r['cost_per_km'] ?? -1)->values();

        return view('templates.fleet.reports.partials.cost_per_km_table', compact('rows'));
    }

    // ---------- Driver Performance ----------

    private function driverOptions()
    {
        $driverLookupId = \App\Models\Lookup::where('type', 'employee-professional')->where('name', 'Driver')->value('id');
        return $driverLookupId ? \App\Models\Employee::where('professional', $driverLookupId)->orderBy('Employee_Name')->get() : collect();
    }

    public function driverPerformance(): View
    {
        return $this->nicePage('templates.fleet.reports.driver_performance', 'fleet.reports.driver-performance', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function driverPerformanceData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = $this->driverOptions()->map(function ($driver) use ($subId, $startDate, $endDate) {
            $trips = Itinerary::with('gatePass')->where('driver_employee_id', $driver->Employee_ID)
                ->where('subdepartment_id', $subId)->whereIn('status', ['completed', 'closed'])
                ->whereBetween('start_date', [$startDate, $endDate])->get();

            $distance = $trips->sum(function ($t) {
                $startOdo = $t->gatePass->odometer_reading ?? null;
                return ($startOdo !== null && $t->return_odometer !== null) ? max(0, $t->return_odometer - $startOdo) : 0;
            });

            $incidents = FleetIncident::where('driver_employee_id', $driver->Employee_ID)
                ->where('subdepartment_id', $subId)->whereBetween('incident_date', [$startDate, $endDate])->get();

            return [
                'driver' => $driver, 'trip_count' => $trips->count(), 'distance' => $distance,
                'incident_count' => $incidents->count(), 'incident_cost' => $incidents->sum(fn ($i) => $i->actual_cost ?? $i->estimated_cost ?? 0),
            ];
        })->filter(fn ($r) => $r['trip_count'] > 0 || $r['incident_count'] > 0)->sortByDesc('trip_count')->values();

        return view('templates.fleet.reports.partials.driver_performance_table', compact('rows'));
    }

    // ---------- Odometer Anomaly ----------

    public function odometerAnomaly(): View
    {
        return $this->nicePage('templates.fleet.reports.odometer_anomaly', 'fleet.reports.odometer-anomaly', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function odometerAnomalyData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $thresholdPerDay = (float) $request->query('threshold_km_per_day', 20);

        $rows = Itinerary::with(['vehicle', 'driver', 'gatePass'])
            ->where('subdepartment_id', $subId)->where('status', 'closed')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get()
            ->map(function ($t) {
                $startOdo = $t->gatePass->odometer_reading ?? null;
                $distance = ($startOdo !== null && $t->return_odometer !== null) ? $t->return_odometer - $startOdo : null;
                $days = max(1, \Carbon\Carbon::parse($t->start_date)->diffInDays($t->end_date) + 1);

                return ['itinerary' => $t, 'distance' => $distance, 'days' => $days, 'per_day' => $distance !== null ? round($distance / $days, 1) : null];
            })
            ->filter(fn ($r) => $r['distance'] === null || $r['distance'] < 0 || $r['per_day'] < $request->query('threshold_km_per_day', 20))
            ->values();

        return view('templates.fleet.reports.partials.odometer_anomaly_table', compact('rows'))->with('thresholdPerDay', $thresholdPerDay);
    }

    // ---------- Predictive Maintenance Due ----------

    public function maintenanceDue(): View
    {
        return $this->nicePage('templates.fleet.reports.maintenance_due', 'fleet.reports.maintenance-due', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function maintenanceDueData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $defaultInterval = (int) $request->query('default_interval_km', 5000);
        $dueSoonThreshold = (int) $request->query('due_soon_km', 1000);

        $rows = Vehicle::where('subdepartment_id', $subId)->get()->map(function ($v) use ($defaultInterval, $dueSoonThreshold) {
            $orders = MaintenanceOrder::where('vehicle_id', $v->id)->whereNotNull('odometer_at_order')
                ->orderBy('odometer_at_order')->pluck('odometer_at_order');

            if ($orders->count() < 2) {
                return ['vehicle' => $v, 'has_history' => false, 'avg_interval' => null, 'next_due' => null, 'remaining' => null, 'estimated' => $orders->count() < 1];
            }

            $diffs = $orders->slice(1)->values()->map(fn ($odo, $i) => $odo - $orders[$i]);
            $avgInterval = round($diffs->avg());
            $lastOdo = $orders->last();
            $nextDue = $lastOdo + $avgInterval;
            $remaining = $nextDue - $v->current_odometer;

            return ['vehicle' => $v, 'has_history' => true, 'avg_interval' => $avgInterval, 'next_due' => $nextDue, 'remaining' => $remaining, 'estimated' => false];
        })->sortBy(fn ($r) => $r['remaining'] ?? PHP_INT_MAX)->values();

        return view('templates.fleet.reports.partials.maintenance_due_table', compact('rows', 'dueSoonThreshold'));
    }

    // ---------- Vehicle Downtime ----------

    public function vehicleDowntime(): View
    {
        return $this->nicePage('templates.fleet.reports.vehicle_downtime', 'fleet.reports.vehicle-downtime', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function vehicleDowntimeData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $periodStart = \Carbon\Carbon::parse($startDate);
        $periodEnd = \Carbon\Carbon::parse($endDate);
        $periodDays = max(1, $periodStart->diffInDays($periodEnd) + 1);

        $rows = Vehicle::where('subdepartment_id', $subId)->get()->map(function ($v) use ($periodStart, $periodEnd, $periodDays) {
            $orders = MaintenanceOrder::where('vehicle_id', $v->id)
                ->where(fn ($q) => $q->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->orWhereBetween('completed_at', [$periodStart, $periodEnd])
                    ->orWhere(fn ($q2) => $q2->where('created_at', '<', $periodStart)->where(fn ($q3) => $q3->whereNull('completed_at')->orWhere('completed_at', '>', $periodEnd))))
                ->get();

            $downtimeDays = $orders->sum(function ($o) use ($periodStart, $periodEnd) {
                $start = $o->created_at->max($periodStart);
                $end = ($o->completed_at ?? now())->min($periodEnd);
                return max(0, $start->diffInDays($end) + 1);
            });

            $downtimeDays = min($downtimeDays, $periodDays);

            return ['vehicle' => $v, 'downtime_days' => $downtimeDays, 'available_days' => $periodDays - $downtimeDays, 'availability_pct' => round((($periodDays - $downtimeDays) / $periodDays) * 100, 1)];
        })->sortBy('availability_pct')->values();

        return view('templates.fleet.reports.partials.vehicle_downtime_table', compact('rows', 'periodDays'));
    }

    // ---------- Destination Frequency ----------

    public function destinationFrequency(): View
    {
        return $this->nicePage('templates.fleet.reports.destination_frequency', 'fleet.reports.destination-frequency', ['subdepartments' => $this->eligibleSubdepartments()]);
    }

    public function destinationFrequencyData(Request $request): View
    {
        $subId = (int) $request->query('subdepartment_id', session('active_subdepartment_id'));
        $this->assertEligibleSubdepartment($subId);
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $mainDestinations = Itinerary::where('subdepartment_id', $subId)->whereBetween('start_date', [$startDate, $endDate])->pluck('destination');

        $legDestinations = \App\Models\ItineraryLeg::whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subId)->whereBetween('start_date', [$startDate, $endDate]))
            ->pluck('destination');

        $rows = $mainDestinations->concat($legDestinations)->countBy()->sortDesc()
            ->map(fn ($count, $destination) => ['destination' => $destination, 'count' => $count])->values();

        return view('templates.fleet.reports.partials.destination_frequency_table', compact('rows'));
    }
}
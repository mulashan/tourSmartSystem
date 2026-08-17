<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetIncident;
use App\Models\FuelOpenOrderItem;
use App\Models\Itinerary;
use App\Models\ItineraryFuel;
use App\Models\ItineraryLeg;
use App\Models\MaintenanceOrder;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;

class FleetDashboardController extends Controller
{
    public function index()
    {
        $subId = session('active_subdepartment_id');

        $vehicles = Vehicle::where('subdepartment_id', $subId)->get();
        $vehicleCounts = [
            'total' => $vehicles->count(),
            'available' => $vehicles->where('status', 'available')->count(),
            'on_trip' => $vehicles->where('status', 'on_trip')->count(),
            'maintenance' => $vehicles->whereIn('status', ['maintenance', 'internal_workshop', 'external_workshop'])->count(),
            'inactive' => $vehicles->where('status', 'inactive')->count(),
        ];

        $expiringInsurance = VehicleInsurance::with(['vehicle', 'insuranceType'])
            ->whereHas('vehicle', fn ($q) => $q->where('subdepartment_id', $subId))
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('tbl_vehicle_insurance')->groupBy('vehicle_id');
            })
            ->where('expire_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('expire_date')
            ->get();

        $mainFuelWaiting = Itinerary::where('subdepartment_id', $subId)
            ->whereIn('status', ['assigned', 'ready', 'in_progress'])
            ->whereDoesntHave('fuelAssignments', fn ($q) => $q->whereNull('leg_id'))
            ->count();

        $legFuelWaiting = ItineraryLeg::whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subId))
            ->whereDoesntHave('fuel')->count();

        $fuelAssignedNotIssued = ItineraryFuel::whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subId))
            ->where('status', 'assigned')->count();

        $pendingApproval = Itinerary::where('subdepartment_id', $subId)->where('status', 'pending')->count();
        $awaitingAssignment = Itinerary::where('subdepartment_id', $subId)->where('status', 'approved')->count();
        $awaitingGatePass = Itinerary::where('subdepartment_id', $subId)->where('status', 'in_progress')->whereDoesntHave('gatePass')->count();

        $activeTrips = Itinerary::with(['vehicle', 'driver'])
            ->where('subdepartment_id', $subId)
            ->whereIn('status', ['assigned', 'ready', 'in_progress'])
            ->orderByDesc('id')->limit(8)->get();

        $monthStart = now()->startOfMonth();
        $tripFuelThisMonth = ItineraryFuel::whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subId))
            ->where('status', 'issued')->where('issued_at', '>=', $monthStart)->get();
        $openOrderFuelThisMonth = FuelOpenOrderItem::whereHas('openOrder', fn ($q) => $q->where('subdepartment_id', $subId))
            ->where('recorded_at', '>=', $monthStart)->get();

        $fuelThisMonth = [
            'quantity' => $tripFuelThisMonth->sum('issued_quantity') + $openOrderFuelThisMonth->sum('quantity'),
            'cost' => $tripFuelThisMonth->sum(fn ($f) => $f->issued_quantity * $f->unit_price) + $openOrderFuelThisMonth->sum('total_amount'),
        ];

        $openMaintenance = MaintenanceOrder::with(['vehicle', 'workshop'])
            ->whereHas('vehicle', fn ($q) => $q->where('subdepartment_id', $subId))
            ->where('status', 'open')->orderByDesc('id')->limit(6)->get();

        $recentIncidents = FleetIncident::with(['vehicle', 'driver'])
            ->where('subdepartment_id', $subId)->orderByDesc('id')->limit(6)->get();

        $incidentCostThisMonth = FleetIncident::where('subdepartment_id', $subId)
            ->where('incident_date', '>=', $monthStart->toDateString())
            ->get()->sum(fn ($i) => $i->actual_cost ?? $i->estimated_cost ?? 0);

        return $this->nicePage('templates.fleet.dashboard', 'fleet.dashboard', [
            'vehicleCounts' => $vehicleCounts,
            'expiringInsurance' => $expiringInsurance,
            'mainFuelWaiting' => $mainFuelWaiting,
            'legFuelWaiting' => $legFuelWaiting,
            'fuelAssignedNotIssued' => $fuelAssignedNotIssued,
            'pendingApproval' => $pendingApproval,
            'awaitingAssignment' => $awaitingAssignment,
            'awaitingGatePass' => $awaitingGatePass,
            'activeTrips' => $activeTrips,
            'fuelThisMonth' => $fuelThisMonth,
            'openMaintenance' => $openMaintenance,
            'recentIncidents' => $recentIncidents,
            'incidentCostThisMonth' => $incidentCostThisMonth,
        ]);
    }
}
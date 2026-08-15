<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FuelOpenOrder;
use App\Models\FuelOpenOrderItem;
use App\Models\Itinerary;
use App\Models\ItineraryFuel;
use App\Models\ItineraryLeg;
use App\Models\Lookup;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FuelController extends Controller
{
    // ---------- Itinerary (Trip) Fuel ----------

    public function tripFuelList(): View
    {
        return $this->nicePage('templates.fleet.fuel.trip_list', 'fleet.fuel.trip', [
            'itineraries' => Itinerary::whereIn('status', ['assigned', 'ready', 'in_progress'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->orderByDesc('id')->get(),
            'fuelRecords' => ItineraryFuel::with(['itinerary', 'vehicle', 'fuelSource', 'assignedBy', 'issuedBy'])
                ->whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', session('active_subdepartment_id')))
                ->orderByDesc('id')->get(),
            'fuelSources' => Lookup::ofType('fuel_source')->orderBy('name')->get(),
        ]);
    }

    public function assignQueue(): View
    {
        $subdepartmentId = session('active_subdepartment_id');

        $mainNeeding = Itinerary::whereIn('status', ['assigned', 'ready', 'in_progress'])
            ->where('subdepartment_id', $subdepartmentId)
            ->whereDoesntHave('fuelAssignments', fn ($q) => $q->whereNull('leg_id'))
            ->get()
            ->map(fn ($i) => ['type' => 'main', 'itinerary' => $i, 'leg' => null]);

        $legsNeeding = ItineraryLeg::whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', $subdepartmentId)->whereIn('status', ['assigned', 'ready', 'in_progress']))
            ->whereDoesntHave('fuel')
            ->with('itinerary')
            ->get()
            ->map(fn ($leg) => ['type' => 'leg', 'itinerary' => $leg->itinerary, 'leg' => $leg]);

        return $this->nicePage('templates.fleet.fuel.assign_list', 'fleet.fuel.assign', [
            'queue' => $mainNeeding->concat($legsNeeding)->values(),
            'fuelSources' => Lookup::ofType('fuel_source')->orderBy('name')->get(),
        ]);
    }

    public function assignTripFuel(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);
        abort_unless($itinerary->vehicle_id, 422, 'This itinerary has no vehicle assigned yet.');

        $data = $request->validate([
            'leg_id' => 'nullable|integer|exists:tbl_itinerary_legs,id',
            'fuel_source_id' => 'required|integer|exists:tbl_lookups,id',
            'fuel_type' => 'required|string|max:50',
            'quantity_assigned' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        if (! empty($data['leg_id'])) {
            $leg = ItineraryLeg::findOrFail($data['leg_id']);
            abort_unless($leg->itinerary_id === $itinerary->id, 422, 'Leg does not belong to this itinerary.');
        }

        $record = ItineraryFuel::create([
            'itinerary_id' => $itinerary->id,
            'leg_id' => $data['leg_id'] ?? null,
            'vehicle_id' => $itinerary->vehicle_id,
            'driver_employee_id' => $itinerary->driver_employee_id,
            'fuel_source_id' => $data['fuel_source_id'],
            'fuel_type' => $data['fuel_type'],
            'quantity_assigned' => $data['quantity_assigned'],
            'unit_price' => $data['unit_price'],
            'total_amount' => $data['quantity_assigned'] * $data['unit_price'],
            'odometer_reading' => $data['odometer_reading'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'status' => 'assigned',
            'assigned_by_user_id' => session('user_id'),
            'assigned_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $record->id]);
    }

    public function issueQueue(): View
    {
        return $this->nicePage('templates.fleet.fuel.issue_list', 'fleet.fuel.issue', [
            'records' => ItineraryFuel::with(['itinerary', 'leg', 'vehicle', 'fuelSource'])
                ->whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', session('active_subdepartment_id')))
                ->where('status', 'assigned')
                ->orderByDesc('id')->get(),
        ]);
    }

    public function issueTripFuel(Request $request, ItineraryFuel $fuel): JsonResponse
    {
        abort_unless($fuel->status === 'assigned', 403, 'Only assigned fuel can be issued.');
        abort_unless($fuel->itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['issued_quantity' => 'required|numeric|min:0.01']);

        $fuel->update([
            'status' => 'issued',
            'issued_quantity' => $data['issued_quantity'],
            'issued_by_user_id' => session('user_id'),
            'issued_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function history(): View
    {
        return $this->nicePage('templates.fleet.fuel.history', 'fleet.fuel.history', [
            'itineraries' => Itinerary::with(['vehicle', 'fuelAssignments.leg', 'fuelAssignments.fuelSource', 'fuelAssignments.assignedBy', 'fuelAssignments.issuedBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->whereHas('fuelAssignments')
                ->orderByDesc('id')->get(),
        ]);
    }


    // public function assignTripFuel(Request $request, Itinerary $itinerary): JsonResponse
    // {
    //     abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);
    //     abort_unless($itinerary->vehicle_id, 422, 'This itinerary has no vehicle assigned yet.');

    //     $data = $request->validate([
    //         'leg_id' => 'nullable|integer|exists:tbl_itinerary_legs,id',
    //         'fuel_source_id' => 'required|integer|exists:tbl_lookups,id',
    //         'fuel_type' => 'required|string|max:50',
    //         'quantity_assigned' => 'required|numeric|min:0.01',
    //         'unit_price' => 'required|numeric|min:0',
    //         'odometer_reading' => 'nullable|integer|min:0',
    //         'remarks' => 'nullable|string|max:255',
    //     ]);

    //     $record = ItineraryFuel::create([
    //         'itinerary_id' => $itinerary->id,
    //         'leg_id' => $data['leg_id'] ?? null,
    //         'vehicle_id' => $itinerary->vehicle_id,
    //         'driver_employee_id' => $itinerary->driver_employee_id,
    //         'fuel_source_id' => $data['fuel_source_id'],
    //         'fuel_type' => $data['fuel_type'],
    //         'quantity_assigned' => $data['quantity_assigned'],
    //         'unit_price' => $data['unit_price'],
    //         'total_amount' => $data['quantity_assigned'] * $data['unit_price'],
    //         'odometer_reading' => $data['odometer_reading'] ?? null,
    //         'remarks' => $data['remarks'] ?? null,
    //         'status' => 'assigned',
    //         'assigned_by_user_id' => session('user_id'),
    //         'assigned_at' => now(),
    //     ]);

    //     return response()->json(['success' => true, 'id' => $record->id]);
    // }

    // public function issueTripFuel(Request $request, ItineraryFuel $fuel): JsonResponse
    // {
    //     abort_unless($fuel->status === 'assigned', 403, 'Only assigned fuel can be issued.');
    //     abort_unless($fuel->itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

    //     $data = $request->validate(['issued_quantity' => 'required|numeric|min:0.01']);

    //     $fuel->update([
    //         'status' => 'issued',
    //         'issued_quantity' => $data['issued_quantity'],
    //         'issued_by_user_id' => session('user_id'),
    //         'issued_at' => now(),
    //     ]);

    //     return response()->json(['success' => true]);
    // }

    // ---------- Emergency / Open Fuel Order ----------

    public function openOrderList(): View
    {
        return $this->nicePage('templates.fleet.fuel.open_orders', 'fleet.fuel.open-orders', [
            'orders' => FuelOpenOrder::with(['fuelSource', 'openedBy', 'items'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->orderByDesc('id')->get(),
            'fuelSources' => Lookup::ofType('fuel_source')->orderBy('name')->get(),
            'vehicles' => Vehicle::where('subdepartment_id', session('active_subdepartment_id'))->orderBy('registration_no')->get(),
        ]);
    }

    public function openOrder(Request $request): JsonResponse
    {
        $data = $request->validate(['fuel_source_id' => 'required|integer|exists:tbl_lookups,id']);

        $existing = FuelOpenOrder::where('subdepartment_id', session('active_subdepartment_id'))
            ->where('fuel_source_id', $data['fuel_source_id'])->where('status', 'open')->exists();

        if ($existing) {
            return response()->json(['message' => 'An open order already exists for this fuel station. Close it before opening a new one.'], 422);
        }

        $order = FuelOpenOrder::create([
            'subdepartment_id' => session('active_subdepartment_id'),
            'fuel_source_id' => $data['fuel_source_id'],
            'status' => 'open',
            'opened_by_user_id' => session('user_id'),
            'opened_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $order->id]);
    }

    public function addOpenOrderItem(Request $request, FuelOpenOrder $order): JsonResponse
    {
        abort_unless($order->status === 'open', 403, 'This order is already closed.');
        abort_unless($order->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'vehicle_id' => 'required|integer|exists:tbl_vehicles,id',
            'driver_employee_id' => 'nullable|integer|exists:tbl_employee,Employee_ID',
            'fuel_type' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
        ]);

        FuelOpenOrderItem::create([
            'open_order_id' => $order->id,
            'vehicle_id' => $data['vehicle_id'],
            'driver_employee_id' => $data['driver_employee_id'] ?? null,
            'fuel_type' => $data['fuel_type'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'total_amount' => $data['quantity'] * $data['unit_price'],
            'odometer_reading' => $data['odometer_reading'] ?? null,
            'recorded_by_user_id' => session('user_id'),
            'recorded_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function closeOpenOrder(FuelOpenOrder $order): JsonResponse
    {
        abort_unless($order->status === 'open', 403, 'This order is already closed.');
        abort_unless($order->subdepartment_id === session('active_subdepartment_id'), 403);

        $order->update(['status' => 'closed', 'closed_by_user_id' => session('user_id'), 'closed_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ---------- Reconciliation ----------

    public function reconciliation(Request $request): View
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $orders = FuelOpenOrder::with(['fuelSource', 'items.vehicle', 'openedBy', 'closedBy'])
            ->where('subdepartment_id', session('active_subdepartment_id'))
            ->whereBetween('opened_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
            ->orderByDesc('opened_at')
            ->get()
            ->map(function ($order) {
                $order->total_quantity = $order->items->sum('quantity');
                $order->total_amount = $order->items->sum('total_amount');
                return $order;
            });

        return $this->nicePage('templates.fleet.fuel.reconciliation', 'fleet.fuel.reconciliation', compact('orders', 'startDate', 'endDate'));
    }

    public function showOpenOrder(FuelOpenOrder $order): View
    {
        abort_unless($order->subdepartment_id === session('active_subdepartment_id'), 403);

        $order->load(['fuelSource', 'openedBy', 'closedBy', 'items.vehicle', 'items.driver', 'items.recordedBy']);

        return $this->nicePage('templates.fleet.fuel.open_order_show', 'fleet.fuel.open-orders', compact('order'));
    }
}
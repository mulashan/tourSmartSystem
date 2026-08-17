<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Lookup;
use App\Models\Vehicle;
use App\Models\VehicleDriverHistory;
use App\Models\VehicleRentalAgreement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleController extends Controller
{
    private function driverOptions()
    {
        $driverLookupId = Lookup::where('type', 'employee-professional')->where('name', 'Driver')->value('id');

        if (! $driverLookupId) {
            return collect();
        }

        return Employee::where('professional', $driverLookupId)->orderBy('Employee_Name')->get();
    }

    public function index(): View
    {
        return $this->nicePage('templates.fleet.vehicles.index', 'fleet.vehicles', [
            'vehicles' => Vehicle::with(['ownershipType', 'currentLocation', 'assignedDriver'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.fleet.vehicles.create', 'fleet.vehicles', [
            'ownershipTypes' => Lookup::ofType('ownership_type')->orderBy('name')->get(),
            'locations' => Lookup::ofType('fleet_location')->orderBy('name')->get(),
            'drivers' => $this->driverOptions(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'registration_no' => 'required|string|max:50|unique:tbl_vehicles,registration_no',
            'vehicle_type' => 'nullable|string|max:100',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1950|max:' . (now()->year + 1),
            'chassis_no' => 'nullable|string|max:100',
            'engine_no' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'seating_capacity' => 'nullable|integer|min:1',
            'fuel_type' => 'nullable|string|max:50',
            'ownership_type_id' => 'required|integer|exists:tbl_lookups,id',
            'owner' => 'nullable|string|max:150',
            'current_location_id' => 'nullable|integer|exists:tbl_lookups,id',
            'current_odometer' => 'nullable|integer|min:0',
            'status' => 'required|in:available,on_trip,internal_workshop,external_workshop,maintenance,inactive',
            'assigned_driver_employee_id' => 'nullable|integer|exists:tbl_employee,Employee_ID',
            'is_active' => 'nullable|boolean',
            'rental_start_date' => 'nullable|date',
            'rental_end_date' => 'nullable|date|after_or_equal:rental_start_date',
            'rental_agreement_document' => 'nullable|file|max:5120',
            'rental_contact_info' => 'nullable|string|max:255',
        ]);

        $vehicle = DB::transaction(function () use ($data, $request) {
            $nextNumber = (Vehicle::max('id') ?? 0) + 1;

            $vehicle = Vehicle::create([
                'vehicle_code' => 'VEH-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT),
                'registration_no' => $data['registration_no'],
                'vehicle_type' => $data['vehicle_type'] ?? null,
                'make' => $data['make'] ?? null,
                'model' => $data['model'] ?? null,
                'year' => $data['year'] ?? null,
                'chassis_no' => $data['chassis_no'] ?? null,
                'engine_no' => $data['engine_no'] ?? null,
                'color' => $data['color'] ?? null,
                'seating_capacity' => $data['seating_capacity'] ?? null,
                'fuel_type' => $data['fuel_type'] ?? null,
                'ownership_type_id' => $data['ownership_type_id'],
                'owner' => $data['owner'] ?? null,
                'current_location_id' => $data['current_location_id'] ?? null,
                'current_odometer' => $data['current_odometer'] ?? 0,
                'purchase_odometer' => $data['current_odometer'] ?? 0,
                'status' => $data['status'],
                'assigned_driver_employee_id' => $data['assigned_driver_employee_id'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'subdepartment_id' => session('active_subdepartment_id'),
                'created_by_user_id' => session('user_id'),
            ]);

            if (! empty($data['assigned_driver_employee_id'])) {
                VehicleDriverHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'employee_id' => $data['assigned_driver_employee_id'],
                    'assigned_by_user_id' => session('user_id'),
                    'assigned_at' => now(),
                ]);
            }

            if (! empty($data['owner']) && ! empty($data['rental_start_date'])) {
                $documentPath = $request->hasFile('rental_agreement_document')
                    ? $request->file('rental_agreement_document')->store('vehicle-agreements', 'public')
                    : null;

                VehicleRentalAgreement::create([
                    'vehicle_id' => $vehicle->id,
                    'owner' => $data['owner'],
                    'start_date' => $data['rental_start_date'],
                    'end_date' => $data['rental_end_date'] ?? null,
                    'agreement_document' => $documentPath,
                    'contact_info' => $data['rental_contact_info'] ?? null,
                    'created_by_user_id' => session('user_id'),
                ]);
            }

            return $vehicle;
        });

        return redirect()->route('fleet.vehicles.show', $vehicle->id)->with('success', 'Vehicle registered successfully.');
    }

    public function show(Vehicle $vehicle): View
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $vehicle->load(['ownershipType', 'currentLocation', 'assignedDriver', 'rentalAgreements.createdBy', 'driverHistory.employee', 'driverHistory.assignedBy']);

        $trips = \App\Models\Itinerary::with(['driver', 'gatePass', 'legs.fuel'])
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['completed', 'closed'])
            ->orderByDesc('id')->get();

        return $this->nicePage('templates.fleet.vehicles.show', 'fleet.vehicles', [
            'vehicle' => $vehicle,
            'drivers' => $this->driverOptions(),
            'trips' => $trips,
        ]);
    }

    public function assignDriver(Request $request, Vehicle $vehicle): JsonResponse
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['employee_id' => 'nullable|integer|exists:tbl_employee,Employee_ID']);

        DB::transaction(function () use ($vehicle, $data) {
            // Close out the previous open assignment, if any.
            VehicleDriverHistory::where('vehicle_id', $vehicle->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            if (! empty($data['employee_id'])) {
                VehicleDriverHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'employee_id' => $data['employee_id'],
                    'assigned_by_user_id' => session('user_id'),
                    'assigned_at' => now(),
                ]);
            }

            $vehicle->update(['assigned_driver_employee_id' => $data['employee_id'] ?? null]);
        });

        return response()->json(['success' => true]);
    }

    public function storeRentalAgreement(Request $request, Vehicle $vehicle): JsonResponse
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'owner' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'agreement_document' => 'nullable|file|max:5120',
            'contact_info' => 'nullable|string|max:255',
        ]);

        $documentPath = $request->hasFile('agreement_document')
            ? $request->file('agreement_document')->store('vehicle-agreements', 'public')
            : null;

        VehicleRentalAgreement::create([
            'vehicle_id' => $vehicle->id,
            'owner' => $data['owner'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'agreement_document' => $documentPath,
            'contact_info' => $data['contact_info'] ?? null,
            'created_by_user_id' => session('user_id'),
        ]);

        $vehicle->update(['owner' => $data['owner']]);

        return response()->json(['success' => true]);
    }

    public function edit(Vehicle $vehicle): View
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        return $this->nicePage('templates.fleet.vehicles.edit', 'fleet.vehicles', [
            'vehicle' => $vehicle,
            'ownershipTypes' => Lookup::ofType('ownership_type')->orderBy('name')->get(),
            'locations' => Lookup::ofType('fleet_location')->orderBy('name')->get(),
            'drivers' => $this->driverOptions(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): \Illuminate\Http\RedirectResponse
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'registration_no' => 'required|string|max:50|unique:tbl_vehicles,registration_no,' . $vehicle->id,
            'vehicle_type' => 'nullable|string|max:100',
            'make' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1950|max:' . (now()->year + 1),
            'chassis_no' => 'nullable|string|max:100',
            'engine_no' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'seating_capacity' => 'nullable|integer|min:1',
            'fuel_type' => 'nullable|string|max:50',
            'ownership_type_id' => 'required|integer|exists:tbl_lookups,id',
            'current_location_id' => 'nullable|integer|exists:tbl_lookups,id',
            'current_odometer' => 'nullable|integer|min:0',
            'status' => 'required|in:available,on_trip,internal_workshop,external_workshop,maintenance,inactive',
            'assigned_driver_employee_id' => 'nullable|integer|exists:tbl_employee,Employee_ID',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($data['current_odometer']) && $data['current_odometer'] < $vehicle->current_odometer) {
            return back()->withErrors(['current_odometer' => "Odometer cannot be set below the last recorded reading ({$vehicle->current_odometer})."])->withInput();
        }

        //$data['is_active'] = $request->boolean('is_active', true);
        $data['is_active'] = $request->boolean('is_active', true);
       // $vehicle->update($data);

        DB::transaction(function () use ($vehicle, $data) {
            if ((int) ($data['assigned_driver_employee_id'] ?? 0) !== (int) ($vehicle->assigned_driver_employee_id ?? 0)) {
                VehicleDriverHistory::where('vehicle_id', $vehicle->id)->whereNull('unassigned_at')->update(['unassigned_at' => now()]);

                if (! empty($data['assigned_driver_employee_id'])) {
                    VehicleDriverHistory::create([
                        'vehicle_id' => $vehicle->id,
                        'employee_id' => $data['assigned_driver_employee_id'],
                        'assigned_by_user_id' => session('user_id'),
                        'assigned_at' => now(),
                    ]);
                }
            }

            $vehicle->update($data);
        });

        return redirect()->route('fleet.vehicles.show', $vehicle->id)->with('success', 'Vehicle updated.');
    }
}
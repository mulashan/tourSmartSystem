<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryLeg;
use App\Models\Lookup;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ItineraryController extends Controller
{
    private function driverOptions()
    {
        $driverLookupId = Lookup::where('type', 'employee-professional')->where('name', 'Driver')->value('id');
        return $driverLookupId ? \App\Models\Employee::where('professional', $driverLookupId)->orderBy('Employee_Name')->get() : collect();
    }

    public function newList(): View
    {
        return $this->nicePage('templates.fleet.itineraries.new_list', 'fleet.itineraries.new', [
            'itineraries' => Itinerary::where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending')->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->nicePage('templates.fleet.itineraries.create', 'fleet.itineraries.new', [
            'destinations' => Lookup::ofType('destination')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clients' => 'required|string|max:500',
            'start_point' => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'return_point' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'comments' => 'nullable|string|max:1000',
            'legs' => 'nullable|array',
            'legs.*.start_point' => 'required_with:legs|string|max:150',
            'legs.*.destination' => 'required_with:legs|string|max:150',
            'legs.*.leg_date' => 'nullable|date',
            'legs.*.notes' => 'nullable|string|max:255',
        ]);

        $itinerary = DB::transaction(function () use ($data) {
            $nextNumber = (Itinerary::max('id') ?? 0) + 1;

            $itinerary = Itinerary::create([
                'trip_number' => 'TRIP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT),
                'subdepartment_id' => session('active_subdepartment_id'),
                'clients' => $data['clients'],
                'start_point' => $data['start_point'],
                'destination' => $data['destination'],
                'return_point' => $data['return_point'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'comments' => $data['comments'] ?? null,
                'status' => 'pending',
                'created_by_user_id' => session('user_id'),
            ]);

            foreach ($data['legs'] ?? [] as $i => $leg) {
                ItineraryLeg::create([
                    'itinerary_id' => $itinerary->id,
                    'leg_number' => $i + 1,
                    'start_point' => $leg['start_point'],
                    'destination' => $leg['destination'],
                    'leg_date' => $leg['leg_date'] ?? null,
                    'notes' => $leg['notes'] ?? null,
                ]);
            }

            return $itinerary;
        });
        //return response()->json(['success' => true, 'id' => $itinerary->id]);

        $canApprove = \App\Models\User::find(session('user_id'))?->hasApprovalPermission('itinerary_approval') ?? false;
        return response()->json(['success' => true, 'id' => $itinerary->id, 'can_approve' => $canApprove]);
    }

    public function approveList(): View
    {
        return $this->nicePage('templates.fleet.itineraries.approve_list', 'fleet.itineraries.approve', [
            'itineraries' => Itinerary::with('createdBy')->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'pending')->orderByDesc('id')->get(),
        ]);
    }

    public function approve(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'pending', 403, 'Only pending itineraries can be approved.');
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $credentials = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $approver = User::where('email', $credentials['username'])->first();

        if (! $approver || ! Hash::check($credentials['password'], $approver->password)) {
            return response()->json(['message' => 'Invalid username or password.'], 422);
        }
        if (! $approver->hasApprovalPermission('itinerary_approval')) {
            return response()->json(['message' => 'This user is not authorized to approve Itineraries.'], 403);
        }

        $itinerary->update(['status' => 'approved', 'approved_by_user_id' => $approver->id, 'approved_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function assignList(): View
    {
        return $this->nicePage('templates.fleet.itineraries.assign_list', 'fleet.itineraries.assign', [
            'itineraries' => Itinerary::where('subdepartment_id', session('active_subdepartment_id'))->where('status', 'approved')->orderByDesc('id')->get(),
            'vehicles' => Vehicle::where('subdepartment_id', session('active_subdepartment_id'))->where('status', 'available')->orderBy('registration_no')->get(),
            'drivers' => $this->driverOptions(),
        ]);
    }

    public function assign(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'approved', 403, 'Only approved itineraries can be assigned.');
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'vehicle_id' => 'required|integer|exists:tbl_vehicles,id',
            'driver_employee_id' => 'required|integer|exists:tbl_employee,Employee_ID',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        abort_unless($vehicle->status === 'available', 422, 'Selected vehicle is not currently available.');

        DB::transaction(function () use ($itinerary, $data, $vehicle) {
            $itinerary->update([
                'vehicle_id' => $data['vehicle_id'],
                'driver_employee_id' => $data['driver_employee_id'],
                'status' => 'assigned',
                'assigned_by_user_id' => session('user_id'),
                'assigned_at' => now(),
            ]);

            $vehicle->update(['status' => 'on_trip']);
        });

        return response()->json(['success' => true]);
    }

    public function activeList(): View
    {
        return $this->nicePage('templates.fleet.itineraries.active_list', 'fleet.itineraries.active', [
            'itineraries' => Itinerary::with(['vehicle', 'driver', 'gatePass'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->whereIn('status', ['assigned', 'ready', 'in_progress', 'completed'])
                ->orderByDesc('id')->get(),
            'vehicles' => Vehicle::where('subdepartment_id', session('active_subdepartment_id'))->orderBy('registration_no')->get(),
            'drivers' => $this->driverOptions(),
            'destinations' => Lookup::ofType('destination')->orderBy('name')->get(),
        ]);
    }

    public function markReady(Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'assigned', 403);
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);
        $itinerary->update(['status' => 'ready']);
        return response()->json(['success' => true]);
    }

    public function markInProgress(Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'ready', 403);
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);
        $itinerary->update(['status' => 'in_progress']);
        return response()->json(['success' => true]);
    }

    public function markComplete(Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'in_progress', 403);
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $itinerary->load('fuelAssignments', 'legs', 'gatePass');

        $mainFuel = $itinerary->fuelAssignments->whereNull('leg_id')->first();
        if (! $mainFuel || $mainFuel->status !== 'issued') {
            return response()->json(['message' => 'Main trip fuel must be assigned and issued before completing.'], 422);
        }

        foreach ($itinerary->legs as $leg) {
            $legFuel = $itinerary->fuelAssignments->firstWhere('leg_id', $leg->id);
            if (! $legFuel || $legFuel->status !== 'issued') {
                return response()->json(['message' => "Fuel for leg #{$leg->leg_number} must be assigned and issued before completing."], 422);
            }
        }

        if (! $itinerary->gatePass || ! $itinerary->gatePass->printed_at) {
            return response()->json(['message' => 'Gate Pass must be generated and printed before completing.'], 422);
        }

        $itinerary->update(['status' => 'completed']);
        return response()->json(['success' => true]);
    }

    public function close(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'completed', 403, 'Only completed trips can be closed.');
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['return_odometer' => 'required|integer|min:0']);

        DB::transaction(function () use ($itinerary, $data) {
            $itinerary->update([
                'status' => 'closed', 'return_odometer' => $data['return_odometer'],
                'closed_by_user_id' => session('user_id'), 'closed_at' => now(),
            ]);

            $itinerary->vehicle?->update(['status' => 'available', 'current_odometer' => $data['return_odometer']]);
        });

        return response()->json(['success' => true]);
    }

    public function cancel(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless(in_array($itinerary->status, ['pending', 'approved', 'assigned', 'ready'], true), 403, 'This itinerary can no longer be cancelled.');
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['reason' => 'required|string|max:255']);

        DB::transaction(function () use ($itinerary, $data) {
            if ($itinerary->vehicle_id) {
                $itinerary->vehicle?->update(['status' => 'available']);
            }

            $itinerary->update([
                'status' => 'cancelled', 'cancel_reason' => $data['reason'],
                'cancelled_by_user_id' => session('user_id'), 'cancelled_at' => now(),
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function preview(Itinerary $itinerary): View
    {
        $itinerary->load(['legs', 'vehicle', 'driver', 'createdBy', 'approvedBy', 'subdepartment.department.branch.company']);
        $branch = $itinerary->subdepartment?->department?->branch;

        return view('templates.fleet.itineraries.preview', ['itinerary' => $itinerary, 'branch' => $branch, 'company' => $branch?->company]);
    }

    public function reassign(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless(in_array($itinerary->status, ['assigned', 'ready', 'in_progress'], true), 403, 'This trip cannot be reassigned at its current stage.');
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'vehicle_id' => 'required|integer|exists:tbl_vehicles,id',
            'driver_employee_id' => 'required|integer|exists:tbl_employee,Employee_ID',
        ]);

        $newVehicle = Vehicle::findOrFail($data['vehicle_id']);

        if ($newVehicle->id != $itinerary->vehicle_id) {
            abort_unless($newVehicle->status === 'available', 422, 'Selected vehicle is not currently available.');
        }

        DB::transaction(function () use ($itinerary, $data, $newVehicle) {
            $oldVehicleId = $itinerary->vehicle_id;

            $itinerary->update([
                'vehicle_id' => $data['vehicle_id'],
                'driver_employee_id' => $data['driver_employee_id'],
                'assigned_by_user_id' => session('user_id'),
                'assigned_at' => now(),
            ]);

            if ($oldVehicleId && $oldVehicleId != $data['vehicle_id']) {
                Vehicle::find($oldVehicleId)?->update(['status' => 'available']);
            }

            $newVehicle->update(['status' => 'on_trip']);
        });

        return response()->json(['success' => true]);
    }

    public function addLeg(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->status === 'in_progress', 403, 'Legs can only be added while the trip is in progress.');
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'start_point' => 'required|string|max:150',
            'destination' => 'required|string|max:150',
            'leg_date' => 'nullable|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $nextLegNumber = ($itinerary->legs()->max('leg_number') ?? 0) + 1;

        $leg = ItineraryLeg::create([
            'itinerary_id' => $itinerary->id,
            'leg_number' => $nextLegNumber,
            'start_point' => $data['start_point'],
            'destination' => $data['destination'],
            'leg_date' => $data['leg_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'leg_id' => $leg->id]);
    }
}
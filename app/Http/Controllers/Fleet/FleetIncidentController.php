<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\FleetIncident;
use App\Models\FleetIncidentPhoto;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FleetIncidentController extends Controller
{
    public function index(): View
    {
        return $this->nicePage('templates.fleet.incidents.index', 'fleet.incidents', [
            'incidents' => FleetIncident::with(['vehicle', 'driver', 'createdBy'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->orderByDesc('id')->get(),
            'vehicles' => Vehicle::where('subdepartment_id', session('active_subdepartment_id'))->orderBy('registration_no')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:accident,road_fine',
            'vehicle_id' => 'required|integer|exists:tbl_vehicles,id',
            'driver_employee_id' => 'nullable|integer|exists:tbl_employee,Employee_ID',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'police_report' => 'nullable|string|max:100',
            'injuries' => 'nullable|string|max:1000',
            'damages' => 'nullable|string|max:1000',
            'covered_by' => 'nullable|in:company,insurance,driver',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'photos.*' => 'nullable|image|max:5120',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $incident = DB::transaction(function () use ($data, $request) {
            $incident = FleetIncident::create([
                ...collect($data)->except('photos')->all(),
                'status' => 'open',
                'subdepartment_id' => session('active_subdepartment_id'),
                'created_by_user_id' => session('user_id'),
            ]);

            foreach ($request->file('photos', []) as $photo) {
                FleetIncidentPhoto::create([
                    'incident_id' => $incident->id,
                    'path' => $photo->store('fleet-incidents', 'public'),
                ]);
            }

            return $incident;
        });

        return response()->json(['success' => true, 'id' => $incident->id]);
    }

    public function close(Request $request, FleetIncident $incident): JsonResponse
    {
        abort_unless($incident->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['actual_cost' => 'nullable|numeric|min:0']);

        $incident->update(['status' => 'closed', 'actual_cost' => $data['actual_cost'] ?? $incident->actual_cost]);

        return response()->json(['success' => true]);
    }

    public function show(FleetIncident $incident): View
    {
        abort_unless($incident->subdepartment_id === session('active_subdepartment_id'), 403);
        $incident->load(['vehicle', 'driver', 'createdBy', 'photos']);

        return $this->nicePage('templates.fleet.incidents.show', 'fleet.incidents', compact('incident'));
    }
}
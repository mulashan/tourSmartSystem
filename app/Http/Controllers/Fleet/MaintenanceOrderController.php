<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceOrder;
use App\Models\Subdepartment;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaintenanceOrderController extends Controller
{
    public function index(): View
    {
        return $this->nicePage('templates.fleet.maintenance.index', 'fleet.maintenance', [
            'orders' => MaintenanceOrder::with(['vehicle', 'driver', 'workshop', 'createdBy'])
                ->whereHas('vehicle', fn ($q) => $q->where('subdepartment_id', session('active_subdepartment_id')))
                ->orderByDesc('id')->get(),
            'vehicles' => Vehicle::where('subdepartment_id', session('active_subdepartment_id'))->orderBy('registration_no')->get(),
            'workshops' => Subdepartment::whereHas('department.departmentNature', fn ($q) => $q->where('department_nature', 'Maintenance'))
                ->orderBy('Subdepartment_Name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_id' => 'required|integer|exists:tbl_vehicles,id',
            'driver_employee_id' => 'nullable|integer|exists:tbl_employee,Employee_ID',
            'problem' => 'required|string|max:1000',
            'workshop_subdepartment_id' => 'required|integer|exists:tbl_subdepartment,Subdepartment_ID',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $order = DB::transaction(function () use ($data, $vehicle) {
            $order = MaintenanceOrder::create([
                'vehicle_id' => $data['vehicle_id'],
                'driver_employee_id' => $data['driver_employee_id'] ?? $vehicle->assigned_driver_employee_id,
                'problem' => $data['problem'],
                'workshop_subdepartment_id' => $data['workshop_subdepartment_id'],
                'odometer_at_order' => $vehicle->current_odometer,
                'status' => 'open',
                'created_by_user_id' => session('user_id'),
            ]);

            $vehicle->update(['status' => 'maintenance']);

            return $order;
        });

        return response()->json(['success' => true, 'id' => $order->id]);
    }

    public function complete(Request $request, MaintenanceOrder $order): JsonResponse
    {
        abort_unless($order->status === 'open', 403, 'Only open orders can be completed.');
        abort_unless($order->vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['completion_notes' => 'nullable|string|max:255']);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'status' => 'completed',
                'completed_by_user_id' => session('user_id'),
                'completed_at' => now(),
                'completion_notes' => $data['completion_notes'] ?? null,
            ]);

            $order->vehicle->update(['status' => 'available']);
        });

        return response()->json(['success' => true]);
    }

    public function cancel(Request $request, MaintenanceOrder $order): JsonResponse
    {
        abort_unless($order->status === 'open', 403, 'Only open orders can be cancelled.');
        abort_unless($order->vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate(['completion_notes' => 'required|string|max:255']);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'status' => 'cancelled',
                'completed_by_user_id' => session('user_id'),
                'completed_at' => now(),
                'completion_notes' => $data['completion_notes'],
            ]);

            $order->vehicle->update(['status' => 'available']);
        });

        return response()->json(['success' => true]);
    }
}
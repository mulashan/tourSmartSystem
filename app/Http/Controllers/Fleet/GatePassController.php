<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\GatePass;
use App\Models\Itinerary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GatePassController extends Controller
{
    public function generateList(): View
    {
        return $this->nicePage('templates.fleet.gate_pass.generate_list', 'fleet.gate-pass.generate', [
            'eligible' => Itinerary::with(['vehicle', 'driver'])
                ->where('subdepartment_id', session('active_subdepartment_id'))
                ->where('status', 'in_progress')
                ->whereDoesntHave('gatePass')
                ->get(),
        ]);
    }

    public function generatedList(): View
    {
        return $this->nicePage('templates.fleet.gate_pass.generated_list', 'fleet.gate-pass.generated', [
            'issued' => GatePass::with(['itinerary', 'vehicle', 'driver'])
                ->whereHas('itinerary', fn ($q) => $q->where('subdepartment_id', session('active_subdepartment_id')))
                ->orderByDesc('id')->get(),
        ]);
    }

    // Generating the Gate Pass IS what marks the trip complete — no separate "Mark Complete" step anymore.
    public function generate(Request $request, Itinerary $itinerary): JsonResponse
    {
        abort_unless($itinerary->subdepartment_id === session('active_subdepartment_id'), 403);
        abort_unless($itinerary->status === 'in_progress', 403, 'Only trips currently in progress can have a Gate Pass generated.');
        abort_if($itinerary->gatePass, 409, 'A Gate Pass already exists for this trip.');

        $itinerary->load('fuelAssignments', 'legs');

        $mainFuel = $itinerary->fuelAssignments->whereNull('leg_id')->first();
        if (! $mainFuel || $mainFuel->status !== 'issued') {
            return response()->json(['message' => 'Main trip fuel must be assigned and issued before generating a Gate Pass.'], 422);
        }

        foreach ($itinerary->legs as $leg) {
            $legFuel = $itinerary->fuelAssignments->firstWhere('leg_id', $leg->id);
            if (! $legFuel || $legFuel->status !== 'issued') {
                return response()->json(['message' => "Fuel for leg #{$leg->leg_number} must be assigned and issued before generating a Gate Pass."], 422);
            }
        }

        $data = $request->validate([
            'expected_return' => 'nullable|date',
            'fuel_level' => 'nullable|string|max:30',
            'passengers' => 'nullable|string|max:500',
        ]);

        $nextNumber = (GatePass::max('id') ?? 0) + 1;

        $pass = DB::transaction(function () use ($itinerary, $data, $nextNumber) {
            $pass = GatePass::create([
                'gate_pass_no' => 'GP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT),
                'itinerary_id' => $itinerary->id,
                'vehicle_id' => $itinerary->vehicle_id,
                'driver_employee_id' => $itinerary->driver_employee_id,
                'date_time_out' => now(),
                'expected_return' => $data['expected_return'] ?? null,
                'odometer_reading' => $itinerary->vehicle->current_odometer ?? null,
                'fuel_level' => $data['fuel_level'] ?? null,
                'passengers' => $data['passengers'] ?? null,
                'authorized_by_user_id' => session('user_id'),
                'created_by_user_id' => session('user_id'),
            ]);

            $itinerary->update(['status' => 'completed']);

            return $pass;
        });

        return response()->json(['success' => true, 'id' => $pass->id]);
    }

    public function preview(GatePass $gatePass): View
    {
        $gatePass->load(['itinerary.subdepartment.department.branch.company', 'vehicle', 'driver', 'authorizedBy']);

        $branch = $gatePass->itinerary?->subdepartment?->department?->branch;

        return view('templates.fleet.gate_pass.preview', ['pass' => $gatePass, 'branch' => $branch, 'company' => $branch?->company]);
    }

    public function markPrinted(GatePass $gatePass): JsonResponse
    {
        $gatePass->update(['printed_at' => now()]);
        return response()->json(['success' => true]);
    }
}
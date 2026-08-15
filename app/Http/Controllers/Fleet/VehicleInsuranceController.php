<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Lookup;
use App\Models\Vehicle;
use App\Models\VehicleInsurance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleInsuranceController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::where('subdepartment_id', session('active_subdepartment_id'))
            ->with(['insurances' => fn ($q) => $q->orderByDesc('expire_date')])
            ->get()
            ->map(function ($vehicle) {
                $current = $vehicle->insurances->first();
                $vehicle->current_insurance = $current;

                if ($current) {
                    $daysLeft = (int) round(now()->diffInDays($current->expire_date, false));
                    $vehicle->insurance_alert = $daysLeft < 0 ? 'expired' : ($daysLeft <= 30 ? 'expiring' : null);
                    $vehicle->insurance_days_left = $daysLeft;
                }

                return $vehicle;
            });

        return $this->nicePage('templates.fleet.insurance.index', 'fleet.insurance', compact('vehicles'));
    }

    public function create(Vehicle $vehicle): View
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        return $this->nicePage('templates.fleet.insurance.create', 'fleet.insurance', [
            'vehicle' => $vehicle,
            'insuranceTypes' => Lookup::ofType('insurance_type')->orderBy('name')->get(),
            'coverages' => Lookup::ofType('insurance_coverage')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Vehicle $vehicle): \Illuminate\Http\RedirectResponse
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        $data = $request->validate([
            'insurance_company' => 'required|string|max:150',
            'policy_number' => 'required|string|max:100',
            'insurance_type_id' => 'required|integer|exists:tbl_lookups,id',
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after:start_date',
            'premium' => 'nullable|numeric|min:0',
            'contact' => 'nullable|string|max:255',
            'certificate_document' => 'nullable|file|max:5120',
            'coverage_ids' => 'nullable|array',
            'coverage_ids.*' => 'integer|exists:tbl_lookups,id',
        ]);

        $documentPath = $request->hasFile('certificate_document')
            ? $request->file('certificate_document')->store('vehicle-insurance', 'public')
            : null;

        $insurance = VehicleInsurance::create([
            'vehicle_id' => $vehicle->id,
            'insurance_company' => $data['insurance_company'],
            'policy_number' => $data['policy_number'],
            'insurance_type_id' => $data['insurance_type_id'],
            'start_date' => $data['start_date'],
            'expire_date' => $data['expire_date'],
            'premium' => $data['premium'] ?? null,
            'contact' => $data['contact'] ?? null,
            'certificate_document' => $documentPath,
            'status' => 'active',
            'created_by_user_id' => session('user_id'),
        ]);

        $insurance->coverages()->sync($data['coverage_ids'] ?? []);

        return redirect()->route('fleet.insurance.index')->with('success', 'Insurance record saved.');
    }

    public function history(Vehicle $vehicle): View
    {
        abort_unless($vehicle->subdepartment_id === session('active_subdepartment_id'), 403);

        return $this->nicePage('templates.fleet.insurance.history', 'fleet.insurance', [
            'vehicle' => $vehicle,
            'insurances' => $vehicle->insurances()->with(['insuranceType', 'coverages', 'createdBy'])->orderByDesc('start_date')->get(),
        ]);
    }
}
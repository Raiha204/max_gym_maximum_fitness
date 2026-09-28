<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $maintenances = Maintenance::with('equipment')->latest('reported_at')->get();
        $equipment = Equipment::orderBy('name')->get();
        return view('maintenance.index', compact('maintenances', 'equipment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => ['nullable', 'string'],
            'custom_equipment_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['nullable', 'in:Low,Medium,High,Critical'],
            'issue' => ['required', 'string', 'max:500'],
        ]);

        $eq = null;
        if (!empty($validated['equipment_id']) && $validated['equipment_id'] !== 'custom') {
            $eq = Equipment::find($validated['equipment_id']);
        }

        $equipmentName = $eq ? $eq->name : ($validated['custom_equipment_name'] ?: 'General Facility Equipment');
        $category = $eq ? $eq->category : ($validated['category'] ?: 'Strength Machines');

        Maintenance::create([
            'equipment_id' => $eq?->id,
            'equipment_name' => $equipmentName,
            'category' => $category,
            'priority' => $validated['priority'] ?? 'Medium',
            'issue' => $validated['issue'],
            'status' => 'Open',
            'reported_at' => now(),
        ]);

        if ($eq) {
            $eq->update(['status' => 'Under Maintenance']);
        }

        return back()->with('success', 'Equipment maintenance report logged.');
    }

    public function updateStatus(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Open,In Progress,Resolved'],
        ]);

        $maintenance->update([
            'status' => $validated['status'],
            'resolved_at' => $validated['status'] === 'Resolved' ? now() : null,
        ]);

        if ($maintenance->equipment) {
            $maintenance->equipment->update([
                'status' => $validated['status'] === 'Resolved' ? 'Operational' : 'Under Maintenance',
            ]);
        }

        return back()->with('success', 'Maintenance status updated.');
    }

    public function resolve(Maintenance $maintenance)
    {
        $maintenance->update([
            'status' => 'Resolved',
            'resolved_at' => now(),
        ]);

        if ($maintenance->equipment) {
            $maintenance->equipment->update(['status' => 'Operational']);
        }

        return back()->with('success', 'Maintenance issue resolved.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();
        return back()->with('success', 'Maintenance report deleted.');
    }
}

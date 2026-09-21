<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $records = Maintenance::with('equipment')->latest('maintenance_date')->paginate(10);
        $equipment = Equipment::orderBy('equipment_name')->get();

        return view('maintenance.index', compact('records', 'equipment'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_id' => ['required', 'exists:equipment,id'],
            'description' => ['required', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['maintenance_date'] = now()->toDateString();
        $data['cost'] = $data['cost'] ?? 0;
        $data['status'] = 'reported';

        Maintenance::create($data);

        Equipment::where('id', $data['equipment_id'])->update(['status' => 'needs_repair']);

        return redirect()->route('maintenance.index')->with('success', 'Maintenance issue reported.');
    }

    public function updateStatus(Request $request, Maintenance $maintenance)
    {
        $data = $request->validate([
            'status' => ['required', 'in:reported,in_progress,resolved'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $maintenance->update($data);

        if ($data['status'] === 'resolved') {
            $maintenance->equipment->update(['status' => 'available']);
        }

        return redirect()->route('maintenance.index')->with('success', 'Maintenance record updated.');
    }
}

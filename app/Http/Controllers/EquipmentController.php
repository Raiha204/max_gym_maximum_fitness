<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::latest()->get();
        return view('equipment.index', compact('equipment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:Operational,Out of Order'],
        ]);
        $validated['last_inspected'] = today();
        Equipment::create($validated);

        return back()->with('popup', ['title' => 'Equipment Added', 'message' => 'Added Successfully!', 'sub' => 'The equipment is now in your inventory.']);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'status' => ['required', 'in:Operational,Out of Order'],
        ]);
        $equipment->update($validated);
        return back()->with('success', 'Equipment updated.');
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();
        return back()->with('success', 'Equipment removed.');
    }
}

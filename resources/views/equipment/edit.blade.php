@extends('layouts.app')
@section('title', 'Edit Equipment')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-lg">
    <form method="POST" action="{{ route('equipment.update', $equipment) }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-semibold mb-1">Equipment Name</label>
            <input type="text" name="equipment_name" value="{{ old('equipment_name', $equipment->equipment_name) }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Category</label>
            <input type="text" name="category" value="{{ old('category', $equipment->category) }}"
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                @foreach (['available' => 'Available', 'in_use' => 'In Use', 'needs_repair' => 'Needs Repair', 'missing' => 'Missing'] as $val => $label)
                    <option value="{{ $val }}" @selected($equipment->status === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3 pt-2">
            <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded font-semibold">Update</button>
            <a href="{{ route('equipment.index') }}" class="px-5 py-2 rounded border border-gray-300 text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection

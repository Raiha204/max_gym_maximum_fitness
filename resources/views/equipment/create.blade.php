@extends('layouts.app')
@section('title', 'Add Equipment')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-lg">
    <form method="POST" action="{{ route('equipment.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1">Equipment Name</label>
            <input type="text" name="equipment_name" value="{{ old('equipment_name') }}" required
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Category</label>
            <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Dumbbell, Machine, Cable"
                   class="w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                <option value="available">Available</option>
                <option value="in_use">In Use</option>
                <option value="needs_repair">Needs Repair</option>
                <option value="missing">Missing</option>
            </select>
        </div>
        <div class="flex gap-3 pt-2">
            <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded font-semibold">Save</button>
            <a href="{{ route('equipment.index') }}" class="px-5 py-2 rounded border border-gray-300 text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection

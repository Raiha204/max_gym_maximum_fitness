@extends('layouts.app')
@section('title', 'Equipment')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('equipment.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-semibold">
        + Add Equipment
    </a>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-black text-white uppercase text-xs">
            <tr>
                <th class="text-left px-5 py-3">Name</th>
                <th class="text-left px-5 py-3">Category</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-left px-5 py-3">Maintenance Records</th>
                <th class="text-right px-5 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($equipment as $eq)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium">{{ $eq->equipment_name }}</td>
                    <td class="px-5 py-3">{{ $eq->category ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($eq->status) {
                                'available' => 'bg-green-100 text-green-700',
                                'in_use' => 'bg-blue-100 text-blue-700',
                                'needs_repair' => 'bg-red-100 text-red-700',
                                'missing' => 'bg-gray-300 text-gray-700',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ ucfirst(str_replace('_',' ', $eq->status)) }}</span>
                    </td>
                    <td class="px-5 py-3">{{ $eq->maintenance_records_count }}</td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <a href="{{ route('equipment.edit', $eq) }}" class="text-red-600 font-semibold hover:underline">Edit</a>
                        <form action="{{ route('equipment.destroy', $eq) }}" method="POST" class="inline" onsubmit="return confirm('Delete?');">
                            @csrf @method('DELETE')
                            <button class="text-gray-500 hover:text-black font-semibold">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-gray-400">No equipment listed yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $equipment->links() }}</div>
@endsection

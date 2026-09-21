@extends('layouts.app')
@section('title', 'Equipment Maintenance')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded shadow p-5 lg:col-span-1 h-fit">
        <h2 class="font-bold mb-3 text-sm uppercase text-gray-600">Report Issue</h2>
        <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-3">
            @csrf
            <select name="equipment_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">Select equipment...</option>
                @foreach ($equipment as $eq)
                    <option value="{{ $eq->id }}">{{ $eq->equipment_name }}</option>
                @endforeach
            </select>
            <textarea name="description" placeholder="Describe the issue..." required rows="3"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
            <input type="number" step="0.01" min="0" name="cost" placeholder="Estimated cost (optional)"
                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded font-semibold text-sm">Report Issue</button>
        </form>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-black text-white uppercase text-xs">
                    <tr>
                        <th class="text-left px-5 py-3">Equipment</th>
                        <th class="text-left px-5 py-3">Reported</th>
                        <th class="text-left px-5 py-3">Description</th>
                        <th class="text-right px-5 py-3">Cost</th>
                        <th class="text-left px-5 py-3">Status</th>
                        <th class="text-right px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $r)
                        <tr class="border-t hover:bg-gray-50 align-top">
                            <td class="px-5 py-3 font-medium">{{ $r->equipment->equipment_name }}</td>
                            <td class="px-5 py-3">{{ \Carbon\Carbon::parse($r->maintenance_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-3 max-w-xs">{{ $r->description }}</td>
                            <td class="px-5 py-3 text-right">₱{{ number_format($r->cost, 2) }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = match($r->status) {
                                        'reported' => 'bg-red-100 text-red-700',
                                        'in_progress' => 'bg-yellow-100 text-yellow-700',
                                        'resolved' => 'bg-green-100 text-green-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ ucfirst(str_replace('_',' ', $r->status)) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($r->status !== 'resolved')
                                    <form method="POST" action="{{ route('maintenance.update', $r) }}" class="flex justify-end gap-1">
                                        @csrf @method('PATCH')
                                        <select name="status" class="border border-gray-300 rounded text-xs px-2 py-1" onchange="this.form.submit()">
                                            <option value="reported" @selected($r->status === 'reported')>Reported</option>
                                            <option value="in_progress" @selected($r->status === 'in_progress')>In Progress</option>
                                            <option value="resolved" @selected($r->status === 'resolved')>Resolved</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-6 text-center text-gray-400">No maintenance records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Equipment Maintenance — MAX GYM')

@section('content')
<div class="space-y-4">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-4 sm:px-5">
        <h1 class="text-lg font-bold text-white">Report Equipment Maintenance</h1>
        <p class="text-[10px] text-[#D1D5DB] mt-0.5">Report equipment issues and track repair status across gym facilities.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach([
            ['Open Maintenance Reports', $maintenances->where('status', 'Open')->count(), 'Awaiting repair action', 'border-[#E31B23]'],
            ['In Progress Repairs', $maintenances->where('status', 'In Progress')->count(), 'Currently being serviced', 'border-amber-500'],
            ['Resolved Maintenance', $maintenances->where('status', 'Resolved')->count(), 'Completed repairs', 'border-emerald-600'],
        ] as [$label, $count, $description, $border])
            <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 {{ $border }} p-3.5 shadow-sm">
                <div class="text-[10px] text-[#6B7280] font-semibold">{{ $label }}</div>
                <div class="text-xl font-mono font-bold mt-1">{{ $count }}</div>
                <div class="text-[9px] text-[#6B7280] mt-1">{{ $description }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- Left 4 Cols: Report Equipment Maintenance -->
        <div class="lg:col-span-4 bg-white rounded-xl border border-[#E5E7EB] p-4 shadow-sm">
            <h2 class="text-[12px] font-bold text-[#111111] pb-3 mb-3 border-b border-[#E5E7EB]">Report Equipment Maintenance</h2>
            <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-3 text-[10px]">
                @csrf
                <div>
                    <label class="block font-semibold text-[#111111] mb-1">Select Equipment</label>
                    <select id="maintenance-equipment" name="equipment_id" onchange="toggleCustomEquipment()" class="w-full px-3 py-2 rounded-lg border border-[#D1D5DB] bg-white">
                        <option value="custom">+ Specify Equipment Name Manually</option>
                        @foreach($equipment as $eq)
                            <option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->category }})</option>
                        @endforeach
                    </select>
                </div>
                <div id="custom-equipment-fields" class="space-y-3">
                    <div>
                        <label class="block font-semibold text-[#111111] mb-1">Equipment Name</label>
                        <input type="text" name="custom_equipment_name" placeholder="e.g. Cable Crossover Machine" class="w-full px-3 py-2 rounded-lg border border-[#D1D5DB]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[#111111] mb-1">Equipment Category</label>
                        <select name="category" class="w-full px-3 py-2 rounded-lg border border-[#D1D5DB] bg-white">
                            @foreach(['Cardio', 'Strength Machines', 'Free Weights', 'Benches & Racks', 'Functional & Accessories'] as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-[#111111] mb-1">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 rounded-lg border border-[#D1D5DB] bg-white">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-[#111111] mb-1">Issue / Maintenance Description *</label>
                    <textarea name="issue" rows="3" required placeholder="Describe the equipment issue..." class="w-full px-3 py-2 rounded-lg border border-[#D1D5DB]"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Report Equipment Maintenance</button>
            </form>
        </div>

        <!-- Right 8 Cols: Reported Equipment Maintenance List -->
        <div class="lg:col-span-8 bg-white rounded-xl border border-[#E5E7EB] p-4 space-y-3 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#E5E7EB]">
                <div>
                    <h2 class="text-[12px] font-bold text-[#111111]">Reported Equipment Maintenance Log</h2>
                    <p class="text-[9px] text-[#6B7280] mt-0.5">Track reported issues and repair status ({{ $maintenances->count() }} records)</p>
                </div>
                <div class="flex gap-1">
                    @foreach(['All', 'Open', 'In Progress', 'Resolved'] as $status)
                        <button type="button" onclick="filterMaintenance('{{ $status }}')" data-maintenance-filter="{{ $status }}" class="maintenance-filter px-2 py-1.5 rounded-lg border border-[#D1D5DB] text-[9px] font-semibold {{ $status === 'All' ? 'bg-[#E31B23] text-white' : 'bg-white text-[#111111]' }}">{{ $status }}</button>
                    @endforeach
                </div>
            </div>
            @forelse($maintenances as $ticket)
                <div class="maintenance-ticket p-3 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-[10px]" data-status="{{ $ticket->status }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-[#111111] text-[11px]">{{ $ticket->equipment_name ?? optional($ticket->equipment)->name ?? 'Equipment' }}</span>
                            <span class="px-2 py-0.5 rounded bg-white border border-[#E5E7EB] text-[9px] font-semibold text-[#6B7280]">{{ $ticket->priority ?? 'Medium' }} Priority</span>
                        </div>
                        <div class="text-[#111111] mt-1">{{ $ticket->issue }}</div>
                        <div class="text-[9px] text-[#6B7280] mt-0.5">{{ $ticket->category }} · Reported {{ optional($ticket->reported_at)->format('M j, Y') }} · {{ $ticket->status }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($ticket->status !== 'Resolved')
                            <form method="POST" action="{{ route('maintenance.resolve', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-3 py-1.5 font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Mark Resolved</button>
                            </form>
                        @else
                            <span class="px-2.5 py-1 rounded bg-[#DCFCE7] text-[#166534] font-bold">✓ Resolved</span>
                        @endif
                        <form method="POST" action="{{ route('maintenance.destroy', $ticket) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1.5 rounded bg-[#FEE2E2] text-[#B91C1C] font-semibold">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-[10px] text-[#6B7280] py-8 text-center">No equipment maintenance reports found. Use the form to report an issue.</div>
            @endforelse
        </div>
    </div>
</div>
<script>
    function toggleCustomEquipment() {
        document.getElementById('custom-equipment-fields').classList.toggle('hidden', document.getElementById('maintenance-equipment').value !== 'custom');
    }

    function filterMaintenance(status) {
        document.querySelectorAll('.maintenance-ticket').forEach((ticket) => {
            ticket.classList.toggle('hidden', status !== 'All' && ticket.dataset.status !== status);
        });
        document.querySelectorAll('.maintenance-filter').forEach((button) => {
            const selected = button.dataset.maintenanceFilter === status;
            button.classList.toggle('bg-[#E31B23]', selected);
            button.classList.toggle('text-white', selected);
            button.classList.toggle('bg-white', !selected);
        });
    }

    document.addEventListener('DOMContentLoaded', toggleCustomEquipment);
</script>
@endsection

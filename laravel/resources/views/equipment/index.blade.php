@extends('layouts.app')

@section('title', 'Equipment Inventory — MAX GYM')

@section('content')
<div class="space-y-4">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-4 sm:px-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-white">Equipment Inventory</h1>
            <p class="text-[10px] text-[#D1D5DB] mt-0.5">Categorized gym equipment directory with status management.</p>
        </div>
        <button type="button" onclick="document.getElementById('equipment-form').classList.toggle('hidden')" class="shrink-0 px-3 py-2 text-[10px] font-bold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">+ Add Equipment</button>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([
            ['Total Equipment Items', $equipment->count(), 'Physical units in inventory', 'border-[#727984]'],
            ['Operational', $equipment->where('status', 'Operational')->count(), 'Ready for member use', 'border-[#7C9A83]'],
            ['Under Maintenance', $equipment->where('status', 'Under Maintenance')->count(), 'Currently being serviced', 'border-[#B09A70]'],
            ['Out of Order', $equipment->where('status', 'Out of Order')->count(), 'Requires repair or replacement', 'border-[#A77979]'],
        ] as [$label, $count, $description, $border])
            <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 {{ $border }} p-3.5 shadow-sm">
                <div class="text-[10px] text-[#6B7280] font-semibold">{{ $label }}</div>
                <div class="text-xl font-mono font-bold mt-1">{{ $count }}</div>
                <div class="text-[9px] text-[#6B7280] mt-1">{{ $description }}</div>
            </div>
        @endforeach
    </div>

    <div id="equipment-form" class="{{ $errors->any() ? '' : 'hidden' }} bg-white rounded-xl border border-[#E5E7EB] p-4 shadow-sm">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-[#E5E7EB]">
            <h2 class="text-[12px] font-bold text-[#111111]">Add New Equipment</h2>
            <button type="button" onclick="document.getElementById('equipment-form').classList.add('hidden')" class="text-[10px] font-semibold text-neutral-500">Close</button>
        </div>
        <form method="POST" action="{{ route('equipment.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#111111] mb-1">Category</label>
                <select name="category" class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB] bg-white">
                    @foreach(['Cardio', 'Strength Machines', 'Free Weights', 'Benches & Racks', 'Functional & Accessories'] as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#111111] mb-1">Equipment Name *</label>
                <input type="text" name="name" placeholder="e.g. Commercial Treadmill #2" required class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-[#111111] mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB] bg-white">
                    <option value="Operational">Operational</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Out of Order">Out of Order</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full py-2 text-xs font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">+ Add Equipment</button>
            </div>
        </form>
    </div>

    <section class="bg-white rounded-xl border border-[#E5E7EB] p-3.5 shadow-sm">
        <div class="flex flex-col xl:flex-row gap-3 pb-3 border-b border-[#E5E7EB]">
            <input id="equipment-search" type="search" oninput="applyEquipmentFilters()" placeholder="Search equipment by name or category..." class="flex-1 px-3 py-2 text-[10px] rounded-lg border border-[#D1D5DB]">
            <div class="flex flex-wrap gap-1.5" role="group" aria-label="Filter by status">
                @foreach(['All', 'Operational', 'Under Maintenance', 'Out of Order'] as $status)
                    <button type="button" data-equipment-filter="{{ $status === 'All' ? 'all' : 'status:' . $status }}" onclick="applyEquipmentFilters('{{ $status === 'All' ? 'all' : 'status:' . $status }}')" class="equipment-filter px-2.5 py-1.5 text-[9px] font-semibold rounded-lg border border-[#D1D5DB] {{ $status === 'All' ? 'bg-[#111111] text-white' : 'bg-white text-[#111111]' }}">{{ $status }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap gap-1.5 py-3 border-b border-[#E5E7EB]">
            @foreach(['Cardio', 'Strength Machines', 'Free Weights', 'Benches & Racks', 'Functional & Accessories'] as $category)
                <button type="button" data-equipment-filter="category:{{ $category }}" onclick="applyEquipmentFilters('category:{{ $category }}')" class="equipment-filter px-2.5 py-1.5 text-[9px] font-semibold rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] text-[#111111]">{{ $category }} ({{ $equipment->where('category', $category)->count() }})</button>
            @endforeach
        </div>
        <div id="equipment-list" class="grid grid-cols-1 xl:grid-cols-2 gap-3 pt-3">
            @foreach(['Cardio', 'Strength Machines', 'Free Weights', 'Benches & Racks', 'Functional & Accessories'] as $category)
                <div class="equipment-category bg-white rounded-lg border border-[#E5E7EB] overflow-hidden" data-category="{{ $category }}">
                    <div class="bg-[#F9FAFB] px-3 py-2 border-b border-[#E5E7EB] font-bold text-[10px] text-[#111111] flex justify-between">
                        <span>{{ $category }}</span><span class="font-mono text-neutral-500">{{ $equipment->where('category', $category)->count() }}</span>
                    </div>
                    <div class="p-2 space-y-1.5">
                @forelse($equipment->where('category', $category) as $eq)
                    <div class="equipment-item p-2.5 rounded-lg border border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[10px]" data-name="{{ strtolower($eq->name) }}" data-category="{{ $eq->category }}" data-status="{{ $eq->status }}">
                        <div>
                            <span class="font-bold text-[#111111]">{{ $eq->name }}</span>
                            <span class="block text-[#6B7280] mt-0.5">{{ $eq->status }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('equipment.update', $eq) }}" class="flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded border border-[#D1D5DB] text-xs font-semibold bg-white">
                                    @foreach(['Operational', 'Under Maintenance', 'Out of Order'] as $st)
                                        <option value="{{ $st }}" @selected($eq->status === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route('equipment.destroy', $eq) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded bg-[#FEE2E2] text-[#B91C1C] font-semibold">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-[10px] text-[#6B7280] py-2">No items in this category.</div>
                @endforelse
                    </div>
                </div>
            @endforeach
        </div>
        <div id="equipment-empty" class="hidden text-center py-12">
            <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-[#E31B23]">+</div>
            <h2 class="text-[12px] font-bold text-[#111111]">No Equipment Added Yet</h2>
            <p class="text-[10px] text-[#6B7280] mt-1">Add equipment to build your inventory across gym categories.</p>
            <button type="button" onclick="document.getElementById('equipment-form').classList.remove('hidden')" class="mt-3 px-3 py-2 text-[10px] font-bold text-white bg-[#E31B23] rounded-lg">+ Add First Equipment</button>
        </div>
    </section>
</div>
<script>
    let activeEquipmentFilter = 'all';

    function applyEquipmentFilters(filter = activeEquipmentFilter) {
        activeEquipmentFilter = filter;
        const search = document.getElementById('equipment-search').value.trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.equipment-item').forEach((item) => {
            const matchesSearch = `${item.dataset.name} ${item.dataset.category.toLowerCase()}`.includes(search);
            const matchesFilter = filter === 'all'
                || (filter.startsWith('status:') && item.dataset.status === filter.slice(7))
                || (filter.startsWith('category:') && item.dataset.category === filter.slice(9));
            const visible = matchesSearch && matchesFilter;
            item.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        document.querySelectorAll('.equipment-category').forEach((category) => {
            category.classList.toggle('hidden', !category.querySelector('.equipment-item:not(.hidden)'));
        });
        document.getElementById('equipment-list').classList.toggle('hidden', visibleCount === 0);
        document.getElementById('equipment-empty').classList.toggle('hidden', visibleCount > 0);
        document.querySelectorAll('.equipment-filter').forEach((button) => {
            const selected = button.dataset.equipmentFilter === filter;
            button.classList.toggle('bg-[#111111]', selected);
            button.classList.toggle('text-white', selected);
            button.classList.toggle('bg-white', !selected);
        });
    }

    document.addEventListener('DOMContentLoaded', () => applyEquipmentFilters('all'));
</script>
@endsection

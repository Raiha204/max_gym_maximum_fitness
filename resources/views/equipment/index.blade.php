@extends('layouts.app')

@section('title', 'Equipment Inventory — MAX GYM')

@section('content')
@php
    $equipmentByCategory = $equipment->groupBy('category')->sortKeys();
@endphp
<div class="space-y-4">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-4 sm:px-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-white">Equipment Inventory</h1>
            <p class="text-[10px] text-[#D1D5DB] mt-0.5">Categorized gym equipment inventory. Submit and track repair reports in Maintenance.</p>
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
            @foreach($equipmentByCategory as $categoryEquipment)
                @php($category = $categoryEquipment->first()->category)
                <button type="button" data-equipment-filter="category:{{ \Illuminate\Support\Str::slug($category) }}" onclick="applyEquipmentFilters(this.dataset.equipmentFilter)" class="equipment-filter px-2.5 py-1.5 text-[9px] font-semibold rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] text-[#111111]">{{ $category }} ({{ $categoryEquipment->count() }})</button>
            @endforeach
        </div>
        <div id="equipment-list" class="grid grid-cols-1 xl:grid-cols-2 gap-3 pt-3">
            @foreach($equipmentByCategory as $categoryEquipment)
                @php($category = $categoryEquipment->first()->category)
                <div class="equipment-category bg-white rounded-lg border border-[#E5E7EB] overflow-hidden" data-category-key="{{ \Illuminate\Support\Str::slug($category) }}">
                    <div class="bg-[#F9FAFB] px-3 py-2 border-b border-[#E5E7EB] font-bold text-[10px] text-[#111111] flex justify-between">
                        <span>{{ $category }}</span><span class="font-mono text-neutral-500">{{ $categoryEquipment->count() }}</span>
                    </div>
                    <div class="p-2 space-y-1.5">
                @foreach($categoryEquipment as $eq)
                    <div class="equipment-item p-2.5 rounded-lg border border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[10px]" data-name="{{ strtolower($eq->name) }}" data-category-key="{{ \Illuminate\Support\Str::slug($eq->category) }}" data-category-name="{{ strtolower($eq->category) }}" data-status="{{ $eq->status }}">
                        <div>
                            <span class="font-bold text-[#111111]">{{ $eq->name }}</span>
                            <span class="block text-[#6B7280] mt-0.5">{{ $eq->status }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('equipment.update', $eq) }}" class="flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded border border-[#D1D5DB] text-xs font-semibold bg-white">
                                    @if($eq->status === 'Under Maintenance')
                                        <option value="" selected disabled>Under Maintenance · see Maintenance</option>
                                    @endif
                                    @foreach(['Operational', 'Out of Order'] as $st)
                                        <option value="{{ $st }}" @selected($eq->status === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </form>
                            <form method="POST" action="{{ route('equipment.destroy', $eq) }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" data-equipment-name="{{ $eq->name }}" onclick="openEquipmentDeleteConfirmation(this)" class="px-2.5 py-1 rounded bg-[#FEE2E2] text-[#B91C1C] font-semibold">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
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
<div id="equipment-delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-[#111111]/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="equipment-delete-title" aria-describedby="equipment-delete-description">
    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-white/70 bg-white shadow-2xl shadow-black/30">
        <div class="h-1.5 bg-gradient-to-r from-[#B51219] via-[#E31B23] to-[#F87171]"></div>
        <div class="p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-50 text-[#E31B23] ring-1 ring-red-100">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18.6A1.6 1.6 0 0 0 3.2 21h17.6a1.6 1.6 0 0 0 1.4-2.4L13.7 3.9a1.9 1.9 0 0 0-3.4 0Z"></path>
                    </svg>
                </div>
                <div class="pt-0.5">
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#E31B23]">Permanent action</p>
                    <h2 id="equipment-delete-title" class="mt-1 text-lg font-bold tracking-tight text-[#111111]">Delete equipment?</h2>
                    <p id="equipment-delete-description" class="mt-1.5 text-sm leading-5 text-[#6B7280]">This equipment will be permanently removed from your inventory. You can't undo this action.</p>
                </div>
            </div>
            <div class="mt-5 rounded-xl border border-[#E5E7EB] bg-[#F9FAFB] p-3.5">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-[#6B7280]">Equipment</p>
                <p id="equipment-delete-name" class="mt-1 break-words text-sm font-bold text-[#111111]"></p>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeEquipmentDeleteConfirmation()" class="rounded-lg border border-[#D1D5DB] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300">Keep equipment</button>
                <button id="equipment-delete-confirm" type="button" onclick="confirmEquipmentDelete()" class="rounded-lg bg-[#E31B23] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#B51219] focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2">Delete equipment</button>
            </div>
        </div>
    </div>
</div>
<script>
    let activeEquipmentFilter = 'all';
    let pendingEquipmentDeleteForm = null;
    let equipmentDeleteTrigger = null;

    function openEquipmentDeleteConfirmation(button) {
        equipmentDeleteTrigger = button;
        pendingEquipmentDeleteForm = button.form;
        document.getElementById('equipment-delete-name').textContent = button.dataset.equipmentName;
        const modal = document.getElementById('equipment-delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('equipment-delete-confirm').focus();
    }

    function closeEquipmentDeleteConfirmation() {
        const modal = document.getElementById('equipment-delete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingEquipmentDeleteForm = null;
        if (equipmentDeleteTrigger) {
            equipmentDeleteTrigger.focus();
            equipmentDeleteTrigger = null;
        }
    }

    function confirmEquipmentDelete() {
        if (!pendingEquipmentDeleteForm) {
            return;
        }

        const form = pendingEquipmentDeleteForm;
        pendingEquipmentDeleteForm = null;
        equipmentDeleteTrigger = null;
        form.requestSubmit();
    }

    function applyEquipmentFilters(filter = activeEquipmentFilter) {
        activeEquipmentFilter = filter;
        const search = document.getElementById('equipment-search').value.trim().toLowerCase();
        let visibleCount = 0;

        document.querySelectorAll('.equipment-item').forEach((item) => {
            const matchesSearch = `${item.dataset.name} ${item.dataset.categoryName}`.includes(search);
            const matchesFilter = filter === 'all'
                || (filter.startsWith('status:') && item.dataset.status === filter.slice(7))
                || (filter.startsWith('category:') && item.dataset.categoryKey === filter.slice(9));
            const visible = matchesSearch && matchesFilter;
            item.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        document.querySelectorAll('.equipment-category').forEach((category) => {
            const categoryMatches = !filter.startsWith('category:')
                || category.dataset.categoryKey === filter.slice(9);
            const hasVisibleItems = Array.from(category.querySelectorAll('.equipment-item'))
                .some((item) => item.style.display !== 'none');
            category.style.display = categoryMatches && hasVisibleItems ? '' : 'none';
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

    document.addEventListener('DOMContentLoaded', () => {
        applyEquipmentFilters('all');
        document.getElementById('equipment-delete-modal').addEventListener('click', (event) => {
            if (event.target === event.currentTarget) {
                closeEquipmentDeleteConfirmation();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && pendingEquipmentDeleteForm) {
                closeEquipmentDeleteConfirmation();
            }
        });
    });
</script>
@endsection

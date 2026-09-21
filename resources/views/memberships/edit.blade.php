@extends('layouts.app')
@section('title', 'Edit Member')

@section('content')
<div class="bg-white rounded shadow p-6 max-w-xl">
    <form method="POST" action="{{ route('memberships.update', $membership) }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name', $membership->first_name) }}" required
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name', $membership->last_name) }}" required
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Member Type</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="border border-gray-300 rounded px-4 py-3 flex items-center gap-2 cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                    <input type="radio" name="member_type" value="regular" class="accent-red-600" @checked(old('member_type', $membership->member_type) === 'regular')>
                    <span class="text-sm">Regular — <span class="font-semibold">₱750/month</span></span>
                </label>
                <label class="border border-gray-300 rounded px-4 py-3 flex items-center gap-2 cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                    <input type="radio" name="member_type" value="student" class="accent-red-600" @checked(old('member_type', $membership->member_type) === 'student')>
                    <span class="text-sm">Student — <span class="font-semibold">₱650/month</span></span>
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-1">Changing the type updates the amount due to match the new price. Paid so far: ₱{{ number_format($membership->amount_paid, 2) }}.</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $membership->start_date) }}"
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $membership->end_date) }}"
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>
        </div>
        <script>
            document.getElementById('start_date').addEventListener('change', function () {
                if (!this.value) return;
                const start = new Date(this.value + 'T00:00:00');
                const end = new Date(start);
                end.setMonth(end.getMonth() + 1);
                const yyyy = end.getFullYear();
                const mm = String(end.getMonth() + 1).padStart(2, '0');
                const dd = String(end.getDate()).padStart(2, '0');
                document.getElementById('end_date').value = `${yyyy}-${mm}-${dd}`;
            });
        </script>
        <div class="flex gap-3 pt-2">
            <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded font-semibold">Update</button>
            <a href="{{ route('memberships.index') }}" class="px-5 py-2 rounded border border-gray-300 text-gray-600">Cancel</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded shadow p-5 lg:col-span-1 h-fit">
        <h2 class="font-bold mb-3 text-sm uppercase text-gray-600">Check In</h2>
        <form method="POST" action="{{ route('attendance.store') }}" class="space-y-3">
            @csrf
            <select name="membership_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">Select member...</option>
                @foreach ($memberships as $m)
                    <option value="{{ $m->id }}">{{ $m->member->member_number }} — {{ $m->fullName() }}</option>
                @endforeach
            </select>
            <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded font-semibold text-sm">Record Check-In</button>
        </form>
    </div>

    <div class="lg:col-span-2">
        <form method="GET" class="mb-4">
            <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                   class="border border-gray-300 rounded px-3 py-2 text-sm">
        </form>
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-black text-white uppercase text-xs">
                    <tr>
                        <th class="text-left px-5 py-3">Member</th>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Time</th>
                        <th class="text-right px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $a)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $a->membership->fullName() }}</td>
                            <td class="px-5 py-3">{{ \Carbon\Carbon::parse($a->attendance_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-3">{{ \Carbon\Carbon::parse($a->check_in)->format('h:i A') }}</td>
                            <td class="px-5 py-3 text-right">
                                <form action="{{ route('attendance.destroy', $a) }}" method="POST" onsubmit="return confirm('Remove record?');">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-500 hover:text-black font-semibold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">No attendance records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $attendances->links() }}</div>
    </div>
</div>
@endsection

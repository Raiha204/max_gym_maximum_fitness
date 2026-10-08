@extends('layouts.app')

@section('title', 'Edit Member — MAX GYM')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-neutral-200 p-6">
    <h1 class="text-xl font-bold text-[#0B0B0E] pb-4 mb-5 border-b border-neutral-200">Edit Member Profile ({{ $membership->member_id }})</h1>
    <form method="POST" action="{{ route('memberships.update', $membership) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Surname</label>
                <input type="text" name="surname" value="{{ old('surname', $membership->surname) }}" required class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name', $membership->given_name) }}" required class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300">
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $membership->email) }}" required class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1">Phone</label>
            <input type="tel" name="phone" value="{{ old('phone', $membership->phone) }}" required inputmode="numeric" pattern="[0-9]{11}" maxlength="11" title="Enter exactly 11 digits, such as 09174829910" placeholder="e.g. 09174829910" autocomplete="tel" class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300">
        </div>
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1">Gender</label>
            <select name="gender" class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300 bg-white">
                @foreach(['Male', 'Female', 'Other'] as $g)
                    <option value="{{ $g }}" @selected($membership->gender === $g)>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-neutral-700 mb-1">Date of Birth</label>
            <input type="date" name="date_of_birth" value="{{ optional($membership->date_of_birth)->format('Y-m-d') }}" lang="en-GB" required class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300">
        </div>
        <div class="flex justify-end gap-2 pt-4 border-t border-neutral-200">
            <a href="{{ route('memberships.show', $membership) }}" class="px-4 py-2 text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 rounded-lg">Cancel</a>
            <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-[#E31E24] rounded-lg">Save Changes</button>
        </div>
    </form>
</div>
@endsection

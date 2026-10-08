<?php

namespace App\Http\Controllers;

use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WalkInController extends Controller
{
    public function index(Request $request)
    {
        $method = $request->query('method', 'All');
        $query = WalkIn::latest('paid_at');
        if (in_array($method, ['Cash', 'GCash'], true)) {
            $query->where('payment_method', $method);
        }
        $walkIns = $query->get();

        return view('walkins.index', compact('walkIns', 'method'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'session_type' => ['required', 'in:Regular Walk-In,Student Walk-In'],
            'payment_method' => ['required', 'in:Cash,GCash'],
        ]);

        WalkIn::create([
            'receipt_no' => 'WI-'.Carbon::now(config('app.display_timezone'))->format('Y').'-'.(201 + WalkIn::count()),
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'session_type' => $validated['session_type'],
            'amount' => WalkIn::SESSION_FEES[$validated['session_type']],
            'payment_method' => $validated['payment_method'],
            'paid_at' => Carbon::now('UTC'),
        ]);

        return back()->with('popup', ['title' => 'Walk-In Recorded', 'message' => 'Added Successfully!', 'sub' => $validated['session_type'].' via '.$validated['payment_method'].' has been recorded.']);
    }
}

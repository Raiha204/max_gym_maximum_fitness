<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with('membership.member')
            ->when($request->date, fn ($q, $date) => $q->whereDate('payment_date', $date))
            ->latest('payment_date')
            ->paginate(15)
            ->withQueryString();

        $totalForDay = (clone $payments)->getCollection()->sum('amount');

        return view('payments.index', compact('payments', 'totalForDay'));
    }

    /**
     * Log a walk-in (single day-use) payment. No name needed — just the
     * visitor type, which decides the fixed price (Regular ₱65 / Student ₱50).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'visitor_type' => ['required', 'in:regular,student'],
            'payment_method' => ['required', 'in:cash,gcash,other'],
        ]);

        Payment::create([
            'visitor_type' => $data['visitor_type'],
            'amount' => Payment::WALK_IN_PRICES[$data['visitor_type']],
            'payment_date' => now()->toDateString(),
            'payment_method' => $data['payment_method'],
            'status' => 'completed',
        ]);

        return redirect()->route('payments.index')->with('success', 'Walk-in payment logged.');
    }
}

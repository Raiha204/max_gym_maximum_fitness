<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $method = $request->query('method', 'All');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));

        $query = Payment::with('membership')->latest('paid_at');
        if (in_array($method, ['Cash', 'GCash'], true)) {
            $query->where('payment_method', $method);
        }
        $payments = $query->get();

        $allPayments = Payment::latest('paid_at')->get();
        $allWalkIns = WalkIn::latest('paid_at')->get();

        return view('payments.index', compact(
            'payments',
            'allPayments',
            'allWalkIns',
            'method',
            'selectedDate'
        ));
    }

    public function reports(Request $request)
    {
        $period = $request->query('period', 'month');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));

        $allPayments = Payment::with('membership')->latest('paid_at')->get();
        $allWalkIns = WalkIn::latest('paid_at')->get();

        return view('reports.index', compact(
            'allPayments',
            'allWalkIns',
            'period',
            'selectedDate'
        ));
    }
}

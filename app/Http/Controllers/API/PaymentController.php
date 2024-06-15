<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        return Payment::create($request->all());
    }

    public function show(Payment $payment)
    {
        return $payment;
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'reservation_id' => 'sometimes|exists:reservations,id',
            'user_id' => 'sometimes|exists:users,id',
            'amount' => 'sometimes|numeric',
            'payment_method' => 'sometimes|string',
        ]);

        $payment->update($request->all());

        return $payment;
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['message' => 'Payment deleted successfully']);
    }
}

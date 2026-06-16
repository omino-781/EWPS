<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payment = Payment::create(array_merge($request->validate([
            'event_id' => 'nullable|exists:events,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,card,mpesa,airtel_money',
            'status' => 'required|in:pending,partial,paid,refunded,failed',
        ]), [
            'recorded_by' => $request->user()->id,
            'receipt_number' => 'RCP-'.strtoupper(Str::random(8)),
        ]));

        return response()->json(['message' => 'Payment recorded.', 'data' => $payment], 201);
    }
}

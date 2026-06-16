<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(): View
    {
        $query = Payment::with(['event', 'user', 'vendor']);

        if (auth()->user()->isVendor()) {
            $query->whereHas('vendor', fn ($q) => $q->where('email', auth()->user()->email));
        }

        return view('payments.index', [
            'payments' => $query->latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => 'nullable|exists:events,id',
            'user_id' => 'nullable|exists:users,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,card,mpesa,airtel_money',
            'reference' => 'nullable|string|max:100',
            'status' => 'required|in:pending,partial,paid,refunded,failed',
            'mobile_money_provider' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
        ]);

        if (auth()->user()->isOrganizer() && ! empty($data['event_id'])) {
            $event = Event::findOrFail($data['event_id']);
            abort_unless($event->organizer_id === auth()->id(), 403);
        }

        $payment = Payment::create(array_merge($data, [
            'recorded_by' => auth()->id(),
            'receipt_number' => 'RCP-'.strtoupper(Str::random(8)),
            'paid_at' => $request->status === 'paid' ? now() : null,
        ]));

        if ($payment->user_id) {
            $this->notifications->sendToUser(
                $payment->user,
                'payment_recorded',
                'Payment Recorded',
                "A {$payment->status} payment of {$payment->amount} has been recorded.",
                ['payment_id' => $payment->id]
            );
        }

        if ($payment->vendor_id) {
            $vendorUser = User::where('email', Vendor::find($payment->vendor_id)?->email)->first();
            if ($vendorUser) {
                $this->notifications->sendToUser(
                    $vendorUser,
                    'vendor_payment',
                    'Vendor Payment Update',
                    "A {$payment->status} vendor payment of {$payment->amount} has been recorded.",
                    ['payment_id' => $payment->id]
                );
            }
        }

        return back()->with('success', 'Payment recorded.');
    }
}

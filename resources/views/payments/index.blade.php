@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payments</h2>
    @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
        <i class="bi bi-plus-circle me-2"></i>Record Payment
    </button>
    @endif
</div>

<!-- Payments Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7">
                <tr>
                    <th>Receipt No.</th>
                    <th>Event</th>
                    <th>Payee / User</th>
                    <th>Vendor</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Paid At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td><span class="font-mono fw-bold">{{ $payment->receipt_number }}</span></td>
                    <td>{{ $payment->event?->name ?? '-' }}</td>
                    <td>{{ $payment->user?->name ?? '-' }}</td>
                    <td>{{ $payment->vendor?->name ?? '-' }}</td>
                    <td class="fw-semibold">${{ number_format($payment->amount, 2) }}</td>
                    <td>
                        <span class="text-uppercase small">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                        @if($payment->reference)
                        <div class="small text-muted fs-8">Ref: {{ $payment->reference }}</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'bg-warning text-dark',
                                'partial' => 'bg-info text-dark',
                                'paid' => 'bg-success',
                                'refunded' => 'bg-secondary',
                                'failed' => 'bg-danger',
                            ];
                        @endphp
                        <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>
                        @if($payment->paid_at)
                        {{ $payment->paid_at->format('M d, Y H:i') }}
                        @else
                        <span class="text-muted small">Not Paid</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-credit-card fs-1 d-block mb-3"></i>
                        No payments recorded yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="card-footer bg-white">
        {{ $payments->links() }}
    </div>
    @endif
</div>

<!-- Record Payment Modal -->
@if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
@php
    $events = \App\Models\Event::orderBy('name')->get(['id', 'name']);
    $users = \App\Models\User::orderBy('name')->get(['id', 'name']);
    $vendors = \App\Models\Vendor::where('is_active', true)->orderBy('name')->get(['id', 'name']);
@endphp
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label for="event_id" class="form-label">Event</label>
                        <select name="event_id" id="event_id" class="form-select">
                            <option value="">None / Not linked to Event</option>
                            @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6">
                        <label for="user_id" class="form-label">User / Payee</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">None / Guest</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6">
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <select name="vendor_id" id="vendor_id" class="form-select">
                            <option value="">None / In-house</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required min="0">
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label">Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" required onchange="toggleMobileMoney(this)">
                            @foreach(config('epms.payment_methods') as $method)
                            <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 d-none" id="mobile_money_field">
                        <label for="mobile_money_provider" class="form-label">Mobile Money Provider</label>
                        <input type="text" name="mobile_money_provider" id="mobile_money_provider" class="form-control" placeholder="e.g. Safaricom / Airtel">
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            @foreach(config('epms.payment_statuses') as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="reference" class="form-label">Reference ID</label>
                        <input type="text" name="reference" id="reference" class="form-control" placeholder="e.g. TXN1830491">
                    </div>

                    <div class="col-12">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleMobileMoney(selectElement) {
    const mobileMoneyField = document.getElementById('mobile_money_field');
    if (selectElement.value === 'mpesa' || selectElement.value === 'airtel_money') {
        mobileMoneyField.classList.remove('d-none');
    } else {
        mobileMoneyField.classList.add('d-none');
    }
}
</script>
@endif
@endsection

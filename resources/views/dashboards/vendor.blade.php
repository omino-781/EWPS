@extends('layouts.app')

@section('title', 'Vendor Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1">Vendor Dashboard</h2>
        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('events.index') }}" class="btn btn-primary"><i class="bi bi-calendar-event me-2"></i>Assigned Events</a>
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-credit-card me-2"></i>Payments</a>
    </div>
</div>

@include('dashboards.partials.stats')

<div class="row g-4">
    <div class="col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">Vendor Profile</div>
            <div class="card-body">
                @if($vendorProfile)
                <h5 class="mb-1">{{ $vendorProfile->name }}</h5>
                <div class="text-muted mb-3">{{ $vendorProfile->category?->name ?? 'Uncategorised' }}</div>
                <dl class="row mb-0">
                    <dt class="col-5">Contact</dt>
                    <dd class="col-7">{{ $vendorProfile->contact_person ?? '-' }}</dd>
                    <dt class="col-5">Email</dt>
                    <dd class="col-7">{{ $vendorProfile->email ?? '-' }}</dd>
                    <dt class="col-5">Phone</dt>
                    <dd class="col-7">{{ $vendorProfile->phone ?? '-' }}</dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        <span class="badge {{ $vendorProfile->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $vendorProfile->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </dl>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-shop-window fs-1 d-block mb-3"></i>
                    Vendor profile not linked yet.
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Assigned Events</span>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Event</th><th>Venue</th><th>Start</th><th>Service Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($assignedEvents as $event)
                        <tr>
                            <td><a href="{{ route('events.show', $event) }}">{{ $event->name }}</a></td>
                            <td>{{ $event->venue?->name ?? 'TBD' }}</td>
                            <td>{{ $event->start_date->format('M d, Y') }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($event->service_status ?? 'assigned') }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No assigned events yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Payment Updates</span>
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($payments as $payment)
                <div class="list-group-item d-flex justify-content-between gap-3">
                    <div>
                        <div class="fw-semibold">{{ $payment->event?->name ?? 'Vendor payment' }}</div>
                        <div class="small text-muted">{{ ucfirst($payment->payment_method) }} · {{ ucfirst($payment->status) }}</div>
                    </div>
                    <span class="fw-semibold">${{ number_format($payment->amount, 2) }}</span>
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">No payment updates yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

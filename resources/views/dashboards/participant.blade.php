@extends('layouts.app')

@section('title', 'Attendee Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1">Attendee / Participant Dashboard</h2>
        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}</p>
    </div>
    <a href="{{ route('events.index') }}" class="btn btn-primary"><i class="bi bi-calendar-event me-2"></i>Browse Events</a>
</div>

@include('dashboards.partials.stats')

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card shadow-sm h-100">
            <div class="card-header">My Registrations</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Event</th><th>Venue</th><th>Registered</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $registration)
                        <tr>
                            <td>
                                @if($registration->event)
                                <a href="{{ route('events.show', $registration->event) }}">{{ $registration->event->name }}</a>
                                @else
                                Event
                                @endif
                            </td>
                            <td>{{ $registration->event?->venue?->name ?? 'TBD' }}</td>
                            <td>{{ $registration->registered_at->format('M d, Y') }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($registration->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No registrations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Available Events</span>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($availableEvents as $event)
                <a href="{{ route('events.show', $event) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">{{ $event->name }}</div>
                            <div class="small text-muted">{{ $event->venue?->name ?? 'TBD' }}</div>
                        </div>
                        <span class="small text-muted text-nowrap">{{ $event->start_date->format('M d') }}</span>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-center text-muted py-4">No available events.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

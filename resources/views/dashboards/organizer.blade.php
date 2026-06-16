@extends('layouts.app')

@section('title', 'Organiser Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1">Organiser Dashboard</h2>
        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('events.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Create Event</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-bar-graph me-2"></i>Reports</a>
    </div>
</div>

@include('dashboards.partials.stats')

<div class="row g-4">
    <div class="col-xl-7">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>My Events</span>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Venue</th><th>Start</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($myEvents as $event)
                        <tr>
                            <td><a href="{{ route('events.show', $event) }}">{{ $event->name }}</a></td>
                            <td>{{ $event->venue?->name ?? 'TBD' }}</td>
                            <td>{{ $event->start_date->format('M d, Y') }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($event->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No events yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Open Tasks</span>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">Tasks</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($openTasks as $task)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">{{ $task->title }}</div>
                            <div class="small text-muted">{{ $task->event?->name ?? 'No event' }}</div>
                        </div>
                        <span class="badge bg-light text-dark border align-self-start">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                    </div>
                    @if($task->deadline)
                    <div class="small text-muted mt-1">Due {{ $task->deadline->format('M d, Y') }}</div>
                    @endif
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">No open tasks.</div>
                @endforelse
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">Recent Registrations</div>
            <div class="list-group list-group-flush">
                @forelse($recentRegistrations as $registration)
                <div class="list-group-item">
                    <div class="fw-semibold">{{ $registration->user?->name ?? 'Participant' }}</div>
                    <div class="small text-muted">{{ $registration->event?->name }} · {{ $registration->registered_at->format('M d, Y H:i') }}</div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">No registrations yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

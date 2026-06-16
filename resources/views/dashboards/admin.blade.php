@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-people me-2"></i>Users</a>
        <a href="{{ route('settings.index') }}" class="btn btn-primary"><i class="bi bi-gear me-2"></i>Settings</a>
    </div>
</div>

@include('dashboards.partials.stats')

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent Events</span>
                <a href="{{ route('events.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Organiser</th><th>Venue</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentEvents as $event)
                        <tr>
                            <td><a href="{{ route('events.show', $event) }}">{{ $event->name }}</a></td>
                            <td>{{ $event->organizer?->name ?? '-' }}</td>
                            <td>{{ $event->venue?->name ?? 'TBD' }}</td>
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
    <div class="col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">Admin Areas</div>
            <div class="list-group list-group-flush">
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i>Users</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('venues.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-building me-2"></i>Venues</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('vendors.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-shop me-2"></i>Vendors</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-bar-graph me-2"></i>Reports</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('settings.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-gear me-2"></i>Settings</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

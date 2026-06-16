@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h2 class="mb-4">Dashboard</h2>
<p class="text-muted">Welcome, {{ auth()->user()->name }}</p>
<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
    <div class="col-md-4 col-lg-3">
        <div class="card card-stat shadow-sm">
            <div class="card-body">
                <div class="text-muted text-uppercase small">{{ str_replace('_', ' ', $label) }}</div>
                <div class="fs-3 fw-bold">{{ $value }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="card shadow-sm">
    <div class="card-header">Recent Events</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Category</th><th>Venue</th><th>Start</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($recentEvents as $event)
            <tr>
                <td><a href="{{ route('events.show', $event) }}">{{ $event->name }}</a></td>
                <td>{{ $event->category?->name }}</td>
                <td>{{ $event->venue?->name ?? '-' }}</td>
                <td>{{ $event->start_date->format('M d, Y') }}</td>
                <td><span class="badge bg-secondary">{{ $event->status }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted">No events yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

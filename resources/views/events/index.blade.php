@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Events</h2>
    @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
    <a href="{{ route('events.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Create Event</a>
    @endif
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('events.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search events..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(config('epms.event_statuses') as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Events Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7">
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Venue</th>
                    <th>Dates</th>
                    <th>Organizer</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td>
                        <div class="fw-bold"><a href="{{ route('events.show', $event) }}">{{ $event->name }}</a></div>
                        <small class="text-muted">{{ Str::limit($event->description, 60) }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $event->category_name ?? $event->category?->name ?? 'None' }}</span></td>
                    <td>{{ $event->venue_name ?? $event->venue?->name ?? 'TBD' }}</td>
                    <td>
                        <div class="small fw-semibold">{{ $event->start_date->format('M d, Y H:i') }}</div>
                        <div class="small text-muted">to {{ $event->end_date->format('M d, Y H:i') }}</div>
                    </td>
                    <td>{{ $event->organizer_name ?? $event->organizer?->name }}</td>
                    <td>${{ number_format($event->budget, 2) }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'draft' => 'bg-warning text-dark',
                                'published' => 'bg-success',
                                'ongoing' => 'bg-info text-dark',
                                'completed' => 'bg-secondary',
                                'cancelled' => 'bg-danger',
                            ];
                            $colorClass = $statusColors[$event->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $colorClass }}">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->isAdministrator() || auth()->user()->id === $event->organizer_id)
                            <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                        No events found matching your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div class="card-footer bg-white">
        {{ $events->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

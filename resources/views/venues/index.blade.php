@extends('layouts.app')

@section('title', 'Venues')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Venues</h2>
    @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVenueModal">
        <i class="bi bi-plus-circle me-2"></i>Add Venue
    </button>
    @endif
</div>

<!-- Venues List -->
<div class="row g-4 mb-4">
    @forelse($venues as $venue)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0">{{ $venue->name }}</h5>
                    <span class="badge {{ $venue->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $venue->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-secondary small mb-3">{{ Str::limit($venue->description, 100) }}</p>
                <ul class="list-unstyled mb-4 small text-muted">
                    <li class="mb-1"><i class="bi bi-geo-alt me-2"></i>{{ $venue->address }}{{ $venue->city ? ', '.$venue->city : '' }}</li>
                    <li class="mb-1"><i class="bi bi-people me-2"></i>Capacity: <strong>{{ $venue->capacity }}</strong> guests</li>
                    <li><i class="bi bi-cash me-2"></i>Rate: <strong>${{ number_format($venue->hourly_rate, 2) }}</strong> / hour</li>
                </ul>
                
                @if(auth()->user()->isAdministrator())
                <div class="border-top pt-3 d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm flex-grow-1" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editVenueModal{{ $venue->id }}">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Venue Modal (Administrator Only) -->
    @if(auth()->user()->isAdministrator())
    <div class="modal fade" id="editVenueModal{{ $venue->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('venues.update', $venue->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Venue - {{ $venue->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-12">
                            <label for="name{{ $venue->id }}" class="form-label">Venue Name</label>
                            <input type="text" name="name" id="name{{ $venue->id }}" class="form-control" value="{{ $venue->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="capacity{{ $venue->id }}" class="form-label">Capacity</label>
                            <input type="number" name="capacity" id="capacity{{ $venue->id }}" class="form-control" value="{{ $venue->capacity }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="is_active{{ $venue->id }}" class="form-label">Status</label>
                            <select name="is_active" id="is_active{{ $venue->id }}" class="form-select">
                                <option value="1" {{ $venue->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$venue->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @empty
    <div class="col-12 text-center text-muted py-5">
        <i class="bi bi-building-x fs-1 d-block mb-3"></i>
        No venues recorded yet.
    </div>
    @endforelse
</div>

@if($venues->hasPages())
<div class="d-flex justify-content-center">
    {{ $venues->links() }}
</div>
@endif

<!-- Create Venue Modal -->
@if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
<div class="modal fade" id="createVenueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('venues.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Venue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label for="new_name" class="form-label">Venue Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="new_name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label for="new_address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" name="address" id="new_address" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="new_city" class="form-label">City</label>
                        <input type="text" name="city" id="new_city" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="new_capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="new_capacity" class="form-control" required min="1">
                    </div>
                    <div class="col-md-6">
                        <label for="new_hourly_rate" class="form-label">Hourly Rate ($)</label>
                        <input type="number" step="0.01" name="hourly_rate" id="new_hourly_rate" class="form-control" min="0">
                    </div>
                    <div class="col-12">
                        <label for="new_description" class="form-label">Description</label>
                        <textarea name="description" id="new_description" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Venue</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@extends('layouts.app')

@section('title', 'Edit Event - ' . $event->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('events.show', $event) }}" class="btn btn-link p-0 text-decoration-none text-muted mb-2"><i class="bi bi-arrow-left me-1"></i> Back to Event Details</a>
    <h2>Edit Event</h2>
</div>

<div class="card shadow-sm max-w-4xl">
    <div class="card-body">
        <form method="POST" action="{{ route('events.update', $event) }}" class="row g-3">
            @csrf
            @method('PUT')
            
            <div class="col-md-6">
                <label for="name" class="form-label">Event Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $event->name) }}" required>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $event->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="venue_id" class="form-label">Venue</label>
                <select name="venue_id" id="venue_id" class="form-select @error('venue_id') is-invalid @enderror">
                    <option value="">Online / TBD (No Venue)</option>
                    @foreach($venues as $venue)
                    <option value="{{ $venue->id }}" {{ old('venue_id', $event->venue_id) == $venue->id ? 'selected' : '' }}>{{ $venue->name }} (Cap: {{ $venue->capacity }})</option>
                    @endforeach
                </select>
                @error('venue_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="budget" class="form-label">Budget ($)</label>
                <input type="number" step="0.01" name="budget" id="budget" class="form-control @error('budget') is-invalid @enderror" value="{{ old('budget', $event->budget) }}">
                @error('budget')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="max_attendees" class="form-label">Max Attendees</label>
                <input type="number" name="max_attendees" id="max_attendees" class="form-control @error('max_attendees') is-invalid @enderror" value="{{ old('max_attendees', $event->max_attendees) }}">
                @error('max_attendees')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="start_date" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $event->start_date?->format('Y-m-d\TH:i')) }}" required>
                @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="end_date" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                <input type="datetime-local" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $event->end_date?->format('Y-m-d\TH:i')) }}" required>
                @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach(config('epms.event_statuses') as $status)
                    <option value="{{ $status }}" {{ old('status', $event->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" id="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $event->description) }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

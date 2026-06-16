@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Vendors</h2>
    @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVendorModal">
        <i class="bi bi-plus-circle me-2"></i>Register Vendor
    </button>
    @endif
</div>

<!-- Vendors Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7">
                <tr>
                    <th>Vendor Name</th>
                    <th>Category</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Rating</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                <tr>
                    <td><div class="fw-bold">{{ $vendor->name }}</div></td>
                    <td><span class="badge bg-light text-dark border">{{ $vendor->category?->name }}</span></td>
                    <td>{{ $vendor->contact_person ?? '-' }}</td>
                    <td>{{ $vendor->email ?? '-' }}</td>
                    <td>{{ $vendor->phone ?? '-' }}</td>
                    <td>{{ $vendor->address ?? '-' }}</td>
                    <td>
                        @if($vendor->rating)
                        <span class="text-warning"><i class="bi bi-star-fill me-1"></i>{{ number_format($vendor->rating, 1) }}</span>
                        @else
                        <span class="text-muted small">No rating</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $vendor->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-shop-window fs-1 d-block mb-3"></i>
                        No vendors registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vendors->hasPages())
    <div class="card-footer bg-white">
        {{ $vendors->links() }}
    </div>
    @endif
</div>

<!-- Create Vendor Modal -->
@if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
<div class="modal fade" id="createVendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vendors.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Register New Vendor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Register Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

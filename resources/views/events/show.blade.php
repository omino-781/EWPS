@extends('layouts.app')

@section('title', $event->name)

@section('content')
@php
    $canManageEvent = auth()->user()->isAdministrator() || auth()->id() === $event->organizer_id;
@endphp

<div class="mb-4">
    <a href="{{ route('events.index') }}" class="btn btn-link p-0 text-decoration-none text-muted mb-2"><i class="bi bi-arrow-left me-1"></i> Back to Events</a>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2>{{ $event->name }}</h2>
        <div class="d-flex gap-2">
            @if($canManageEvent)
            <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit</a>
            <form action="{{ route('events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
            </form>
            @endif

            @php
                $userRegistration = $event->registrations->firstWhere('user_id', auth()->id());
                $isRegistered = (bool) $userRegistration;
            @endphp

            @if($isRegistered)
                @if($userRegistration->status === 'attended')
                <span class="btn btn-success disabled"><i class="bi bi-check-circle me-1"></i> Attendance Confirmed</span>
                @else
                <span class="btn btn-success disabled"><i class="bi bi-check-circle me-1"></i> Registered</span>
                @if(auth()->user()->isParticipant())
                <form action="{{ route('events.attendance.confirm', $event) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-success"><i class="bi bi-person-check me-1"></i> Confirm Attendance</button>
                </form>
                @endif
                @endif
                <a href="{{ route('events.ticket', $event) }}" class="btn btn-outline-primary" target="_blank"><i class="bi bi-ticket-perforated me-1"></i> View Ticket</a>
            @elseif(auth()->user()->isParticipant() && $event->status === 'published' && ($event->max_attendees === null || $event->registrations->count() < $event->max_attendees))
            <form action="{{ route('events.register', $event) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Register now</button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main details -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-light text-dark border">{{ $event->category?->name ?? 'None' }}</span>
                    @php
                        $statusColors = [
                            'draft' => 'bg-warning text-dark',
                            'published' => 'bg-success',
                            'ongoing' => 'bg-info text-dark',
                            'completed' => 'bg-secondary',
                            'cancelled' => 'bg-danger',
                        ];
                    @endphp
                    <span class="badge {{ $statusColors[$event->status] ?? 'bg-secondary' }}">{{ ucfirst($event->status) }}</span>
                </div>
                <h5 class="card-title mb-3">About the Event</h5>
                <p class="card-text text-secondary" style="white-space: pre-wrap;">{{ $event->description }}</p>
            </div>
        </div>

        <!-- Tab Panel (Tasks, Attendees, Vendors) -->
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom-0 pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="eventTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks-tab-pane" type="button" role="tab"><i class="bi bi-check2-square me-1"></i> Tasks ({{ $event->tasks->count() }})</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="attendees-tab" data-bs-toggle="tab" data-bs-target="#attendees-tab-pane" type="button" role="tab"><i class="bi bi-people me-1"></i> Attendees ({{ $event->registrations->count() }})</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="vendors-tab" data-bs-toggle="tab" data-bs-target="#vendors-tab-pane" type="button" role="tab"><i class="bi bi-shop me-1"></i> Vendors ({{ $event->vendors->count() }})</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="eventTabsContent">
                    <!-- Tasks Tab -->
                    <div class="tab-pane fade show active" id="tasks-tab-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th>Assignee</th>
                                        <th>Priority</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($event->tasks as $task)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $task->title }}</div>
                                            <small class="text-muted">{{ $task->description }}</small>
                                        </td>
                                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                        <td>
                                            @php
                                                $priorityColors = ['low' => 'bg-info text-dark', 'medium' => 'bg-warning text-dark', 'high' => 'bg-danger'];
                                            @endphp
                                            <span class="badge {{ $priorityColors[$task->priority] ?? 'bg-secondary' }}">{{ ucfirst($task->priority ?? 'medium') }}</span>
                                        </td>
                                        <td>{{ $task->deadline?->format('M d, Y') ?? 'No deadline' }}</td>
                                        <td>
                                            @if(auth()->user()->isAdministrator() || auth()->user()->id === $event->organizer_id || auth()->user()->id === $task->assigned_to)
                                            <form action="{{ route('tasks.status', $task) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm d-inline-block w-auto py-0 px-2" style="font-size: 0.8rem;">
                                                    <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                </select>
                                            </form>
                                            @else
                                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No tasks associated with this event.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Attendees Tab -->
                    <div class="tab-pane fade" id="attendees-tab-pane" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($event->registrations as $registration)
                                    <tr>
                                        <td>{{ $registration->user?->name }}</td>
                                        <td>{{ $registration->user?->email }}</td>
                                        <td>{{ $registration->user?->phone ?? '-' }}</td>
                                        <td>{{ $registration->registered_at->format('M d, Y H:i') }}</td>
                                        <td><span class="badge bg-success">{{ ucfirst($registration->status) }}</span></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No registrations yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Vendors Tab -->
                    <div class="tab-pane fade" id="vendors-tab-pane" role="tabpanel">
                        @if($canManageEvent && $availableVendors->isNotEmpty())
                        <form action="{{ route('events.vendors.assign', $event) }}" method="POST" class="row g-2 align-items-end mb-3">
                            @csrf
                            <div class="col-md-3">
                                <label for="vendor_id" class="form-label">Vendor</label>
                                <select name="vendor_id" id="vendor_id" class="form-select form-select-sm" required>
                                    <option value="">Select vendor</option>
                                    @foreach($availableVendors as $availableVendor)
                                    <option value="{{ $availableVendor->id }}">{{ $availableVendor->name }} - {{ $availableVendor->category?->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="service_description" class="form-label">Service</label>
                                <input type="text" name="service_description" id="service_description" class="form-control form-control-sm" placeholder="e.g. catering, sound, decor">
                            </div>
                            <div class="col-md-2">
                                <label for="contract_amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" min="0" name="contract_amount" id="contract_amount" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="col-md-2">
                                <label for="vendor_status" class="form-label">Status</label>
                                <select name="status" id="vendor_status" class="form-select form-select-sm">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </form>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Vendor Name</th>
                                        <th>Service Description</th>
                                        <th>Contract Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($event->vendors as $vendor)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $vendor->name }}</div>
                                            <small class="text-muted">{{ $vendor->category?->name }}</small>
                                        </td>
                                        <td>{{ $vendor->pivot->service_description ?? '-' }}</td>
                                        <td>${{ number_format($vendor->pivot->contract_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $canUpdateVendorStatus = $canManageEvent || (auth()->user()->isVendor() && strcasecmp((string) $vendor->email, (string) auth()->user()->email) === 0);
                                            @endphp
                                            @if($canUpdateVendorStatus)
                                            <form action="{{ route('events.vendors.status', [$event, $vendor]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                    <option value="pending" {{ $vendor->pivot->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="confirmed" {{ $vendor->pivot->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="completed" {{ $vendor->pivot->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ $vendor->pivot->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </form>
                                            @else
                                            <span class="badge bg-primary">{{ ucfirst($vendor->pivot->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($canManageEvent)
                                            <form action="{{ route('events.vendors.remove', [$event, $vendor]) }}" method="POST" onsubmit="return confirm('Remove this vendor from the event?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No vendors assigned yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Event Info Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Event Information</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small"><i class="bi bi-calendar3 me-1"></i> Start Date</div>
                            {{ $event->start_date->format('M d, Y H:i') }}
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small"><i class="bi bi-calendar3 me-1"></i> End Date</div>
                            {{ $event->end_date->format('M d, Y H:i') }}
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small"><i class="bi bi-geo-alt me-1"></i> Venue</div>
                            @if($event->venue)
                            <div>{{ $event->venue->name }}</div>
                            <small class="text-muted d-block">{{ $event->venue->address }}, {{ $event->venue->city }}</small>
                            @else
                            <span class="text-muted">Online / TBD</span>
                            @endif
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small"><i class="bi bi-person-badge me-1"></i> Organizer</div>
                            {{ $event->organizer?->name }}
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start px-0">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-muted small"><i class="bi bi-people-fill me-1"></i> Capacity</div>
                            @if($event->max_attendees)
                            {{ $event->max_attendees }} attendees max
                            @else
                            Unlimited
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Budget Card -->
        @if($canManageEvent)
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-wallet2 me-1"></i> Financial Overview</h5>
                @if($event->budgetRecord)
                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Allocated Budget</span>
                        <span class="fw-bold">${{ number_format($event->budgetRecord->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Spent Amount</span>
                        <span class="fw-bold text-danger">${{ number_format($event->budgetRecord->spent_amount, 2) }}</span>
                    </div>
                    @php
                        $rem = $event->budgetRecord->total_amount - $event->budgetRecord->spent_amount;
                        $pct = $event->budgetRecord->total_amount > 0 ? ($event->budgetRecord->spent_amount / $event->budgetRecord->total_amount) * 100 : 0;
                    @endphp
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-{{ $pct > 100 ? 'danger' : 'primary' }}" role="progressbar" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between small mt-2">
                        <span>Remaining</span>
                        <span class="fw-bold text-{{ $rem < 0 ? 'danger' : 'success' }}">${{ number_format($rem, 2) }}</span>
                    </div>
                </div>
                @else
                <div class="text-center py-3 text-muted">
                    <p class="small mb-2">No budget recorded for this event yet.</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

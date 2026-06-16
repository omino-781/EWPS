@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tasks</h2>
    @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="bi bi-plus-circle me-2"></i>Assign Task
    </button>
    @endif
</div>

<!-- Tasks Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7">
                <tr>
                    <th>Task</th>
                    <th>Event</th>
                    <th>Assigned To</th>
                    <th>Deadline</th>
                    <th>Priority</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $task->title }}</div>
                        <small class="text-muted">{{ $task->description }}</small>
                    </td>
                    <td>{{ $task->event?->name }}</td>
                    <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                    <td>
                        @if($task->deadline)
                        <span class="{{ $task->deadline->isPast() && $task->status !== 'completed' ? 'text-danger fw-semibold' : '' }}">
                            {{ $task->deadline->format('M d, Y') }}
                        </span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $priorityColors = [
                                'low' => 'bg-info text-dark',
                                'medium' => 'bg-warning text-dark',
                                'high' => 'bg-danger',
                            ];
                        @endphp
                        <span class="badge {{ $priorityColors[$task->priority] ?? 'bg-secondary' }}">
                            {{ ucfirst($task->priority ?? 'medium') }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('tasks.status', $task) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-check2-square fs-1 d-block mb-3"></i>
                        No tasks found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
    <div class="card-footer bg-white">
        {{ $tasks->links() }}
    </div>
    @endif
</div>

<!-- Assign Task Modal -->
@if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
<div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label for="event_id" class="form-label">Event <span class="text-danger">*</span></label>
                        <select name="event_id" id="event_id" class="form-select" required>
                            <option value="">Select Event</option>
                            @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="assigned_to" class="form-label">Assign To</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">Leave Unassigned</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="date" name="deadline" id="deadline" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

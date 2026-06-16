@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<h2 class="mb-4">Reports</h2>

<div class="row g-4">
    <!-- Generate Report Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Generate New Report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="type" class="form-label">Report Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required onchange="toggleEventSelect(this)">
                            <option value="">Select Report Type</option>
                            @foreach($types as $type)
                            <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="event_select_container">
                        <label for="event_id" class="form-label">Event <span class="text-danger">*</span></label>
                        <select name="event_id" id="event_id" class="form-select">
                            <option value="">Select Event</option>
                            @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="format" class="form-label">Format <span class="text-danger">*</span></label>
                        <select name="format" id="format" class="form-select" required>
                            @foreach($formats as $format)
                            <option value="{{ $format }}">{{ strtoupper($format) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Generated Reports History -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Generated Reports History</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7">
                        <tr>
                            <th>Report Details</th>
                            <th>Type</th>
                            <th>Format</th>
                            <th>Generated At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $report->title }}</div>
                                @if($report->event)
                                <small class="text-muted">Event: {{ $report->event->name }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_', ' ', $report->type)) }}</span></td>
                            <td><span class="badge bg-secondary">{{ strtoupper($report->format) }}</span></td>
                            <td>{{ $report->generated_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('reports.download', $report) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-text fs-1 d-block mb-3"></i>
                                You haven't generated any reports yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())
            <div class="card-footer bg-white">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleEventSelect(typeElement) {
    const container = document.getElementById('event_select_container');
    const eventInput = document.getElementById('event_id');
    const typeValue = typeElement.value;
    
    // Check if event ID is required for these types
    if (typeValue === 'attendance' || typeValue === 'financial' || typeValue === 'vendor' || typeValue === 'task_progress') {
        container.classList.remove('d-none');
        eventInput.setAttribute('required', 'required');
    } else {
        container.classList.add('d-none');
        eventInput.removeAttribute('required');
    }
}
</script>
@endsection

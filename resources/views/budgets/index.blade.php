@extends('layouts.app')

@section('title', 'Budgets & Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Budgets & Expenses</h2>
    @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordExpenseModal">
        <i class="bi bi-plus-circle me-2"></i>Record Expense
    </button>
    @endif
</div>

<!-- Budgets Summary Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Event Budgets</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7">
                <tr>
                    <th>Event</th>
                    <th>Total Budget</th>
                    <th>Spent Amount</th>
                    <th>Variance / Remaining</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $budget)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $budget->event?->name }}</div>
                    </td>
                    <td>${{ number_format($budget->total_amount, 2) }}</td>
                    <td class="text-danger fw-semibold">${{ number_format($budget->spent_amount, 2) }}</td>
                    @php
                        $variance = $budget->variance();
                        $pct = $budget->total_amount > 0 ? ($budget->spent_amount / $budget->total_amount) * 100 : 0;
                    @endphp
                    <td class="fw-semibold text-{{ $variance < 0 ? 'danger' : 'success' }}">
                        ${{ number_format($variance, 2) }}
                    </td>
                    <td style="width: 200px;">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $pct > 100 ? 'danger' : ($pct > 80 ? 'warning' : 'primary') }}" role="progressbar" style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                        <small class="text-muted fs-8">{{ number_format($pct, 1) }}% spent</small>
                    </td>
                    <td>
                        <span class="badge bg-{{ $budget->status === 'approved' ? 'success' : ($budget->status === 'pending' ? 'warning text-dark' : 'secondary') }}">
                            {{ ucfirst($budget->status) }}
                        </span>
                    </td>
                    <td>
                        @if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#recordExpenseModal"
                                onclick="prefillBudget('{{ $budget->id }}', '{{ $budget->event_id }}')">
                            <i class="bi bi-receipt me-1"></i>Add Expense
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-wallet2 fs-1 d-block mb-3"></i>
                        No budgets recorded yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($budgets->hasPages())
    <div class="card-footer bg-white">
        {{ $budgets->links() }}
    </div>
    @endif
</div>

<!-- Record Expense Modal -->
@if(auth()->user()->isAdministrator() || auth()->user()->isOrganizer())
@php
    $vendors = \App\Models\Vendor::where('is_active', true)->orderBy('name')->get();
@endphp
<div class="modal fade" id="recordExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label for="event_select" class="form-label">Select Event <span class="text-danger">*</span></label>
                        <select name="event_id" id="event_select" class="form-select" required onchange="updateBudgetId(this)">
                            <option value="">Select Event</option>
                            @foreach($budgets as $b)
                            <option value="{{ $b->event_id }}" data-budget-id="{{ $b->id }}">{{ $b->event?->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="budget_id" id="budget_id_hidden">

                    <div class="col-12">
                        <label for="category" class="form-label">Expense Category <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="catering">Catering</option>
                            <option value="decorations">Decorations</option>
                            <option value="equipment">Equipment Rental</option>
                            <option value="marketing">Marketing & Printing</option>
                            <option value="venue_fee">Venue Fee</option>
                            <option value="entertainment">Entertainment</option>
                            <option value="logistics">Logistics / Transport</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="vendor_id" class="form-label">Vendor (Optional)</label>
                        <select name="vendor_id" id="vendor_id" class="form-select">
                            <option value="">No Vendor Selected</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->category?->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required min="0">
                    </div>

                    <div class="col-md-6">
                        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" id="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" id="description" class="form-control" required placeholder="e.g. Stage sound system rental">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prefillBudget(budgetId, eventId) {
    const eventSelect = document.getElementById('event_select');
    eventSelect.value = eventId;
    document.getElementById('budget_id_hidden').value = budgetId;
}

function updateBudgetId(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const budgetId = selectedOption.getAttribute('data-budget-id');
    document.getElementById('budget_id_hidden').value = budgetId;
}
</script>
@endif
@endsection

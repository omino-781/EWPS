<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Event;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(): View
    {
        $budgetQuery = Budget::with('event');
        $eventQuery = Event::orderBy('name');

        if (auth()->user()->isOrganizer()) {
            $budgetQuery->whereHas('event', fn ($q) => $q->where('organizer_id', auth()->id()));
            $eventQuery->where('organizer_id', auth()->id());
        }

        return view('budgets.index', [
            'budgets' => $budgetQuery->latest()->paginate(15),
            'events' => $eventQuery->get(['id', 'name']),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'event_id' => 'required|exists:events,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:50',
        ]);

        $data['recorded_by'] = auth()->id();

        $budget = Budget::with('event')
            ->where('id', $data['budget_id'])
            ->where('event_id', $data['event_id'])
            ->firstOrFail();

        if (auth()->user()->isOrganizer() && $budget->event?->organizer_id !== auth()->id()) {
            abort(403);
        }

        $expense = Expense::create($data);
        $budget->increment('spent_amount', $expense->amount);

        return back()->with('success', 'Expense recorded.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(): View
    {
        $query = Task::with(['event', 'assignee']);

        if (auth()->user()->isOrganizer()) {
            $query->whereHas('event', fn ($q) => $q->where('organizer_id', auth()->id()));
        }

        return view('tasks.index', [
            'tasks' => $query->latest()->paginate(15),
            'events' => Event::when(auth()->user()->isOrganizer(), fn ($q) => $q->where('organizer_id', auth()->id()))->get(['id', 'name']),
            'users' => User::whereHas('role', fn ($q) => $q->whereIn('slug', ['organizer', 'administrator']))->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'event_id' => 'required|exists:events,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $event = Event::findOrFail($data['event_id']);
        if (auth()->user()->isOrganizer() && $event->organizer_id !== auth()->id()) {
            abort(403);
        }

        Task::create(array_merge($data, ['created_by' => auth()->id(), 'status' => 'pending']));

        return back()->with('success', 'Task assigned.');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $task->loadMissing('event');
        abort_unless(
            auth()->user()->isAdministrator()
                || auth()->id() === $task->event?->organizer_id
                || auth()->id() === $task->assigned_to,
            403
        );

        $request->validate(['status' => 'required|in:pending,in_progress,completed']);

        $task->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return back()->with('success', 'Task status updated.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Venue;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Services\EventService;
use App\Services\NotificationService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected EventRepositoryInterface $events,
        protected EventService $eventService,
        protected RegistrationService $registrationService,
        protected NotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $filters = $request->only(['status', 'category_id', 'search']);
        $user = $request->user();

        if ($user->isOrganizer()) {
            $filters['organizer_id'] = $user->id;
        }

        if ($user->isParticipant()) {
            $filters['published_only'] = true;
        }

        if ($user->isVendor()) {
            $filters['vendor_email'] = $user->email;
        }

        $eventList = $this->events->paginate($filters, $user->isParticipant() ? 10 : 15);
        $categories = Cache::remember(
            'event_filter_categories',
            now()->addMinutes(10),
            fn () => EventCategory::orderBy('name')->get(['id', 'name'])
        );

        return view('events.index', [
            'events' => $eventList,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Event::class);

        return view('events.create', [
            'categories' => EventCategory::orderBy('name')->get(),
            'venues' => Venue::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = array_merge($request->validated(), [
            'organizer_id' => auth()->id(),
            'status' => $request->input('status', 'draft'),
        ]);

        try {
            $event = $this->eventService->create($data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('events.show', $event)->with('success', 'Event created successfully.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['category', 'venue', 'organizer', 'registrations.user', 'tasks.assignee', 'budgetRecord', 'vendors.category']);

        $availableVendors = collect();
        if (auth()->user()->isAdministrator() || auth()->id() === $event->organizer_id) {
            $availableVendors = Vendor::with('category')
                ->where('is_active', true)
                ->whereNotIn('id', $event->vendors->pluck('id'))
                ->orderBy('name')
                ->get();
        }

        return view('events.show', compact('event', 'availableVendors'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('events.edit', [
            'event' => $event,
            'categories' => EventCategory::orderBy('name')->get(),
            'venues' => Venue::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        try {
            $this->eventService->update($event, $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('events.show', $event)->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);
        $this->eventService->delete($event);

        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    public function register(Event $event): RedirectResponse
    {
        $this->authorize('register', $event);

        try {
            $this->registrationService->register($event, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Registration successful.');
    }

    public function confirmAttendance(Event $event): RedirectResponse
    {
        abort_unless(auth()->user()->isParticipant(), 403);

        $registration = $event->registrations()
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($registration->status === 'attended') {
            return back()->with('success', 'Attendance already confirmed.');
        }

        $this->registrationService->checkIn($registration);

        if ($event->organizer) {
            $this->notifications->sendToUser(
                $event->organizer,
                'attendance_confirmed',
                'Attendance Confirmed',
                auth()->user()->name." confirmed attendance for {$event->name}.",
                ['event_id' => $event->id, 'registration_id' => $registration->id]
            );
        }

        return back()->with('success', 'Attendance confirmed.');
    }

    public function assignVendor(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'service_description' => 'nullable|string|max:1000',
            'contract_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $vendor = Vendor::findOrFail($data['vendor_id']);

        $event->vendors()->syncWithoutDetaching([
            $vendor->id => [
                'service_description' => $data['service_description'] ?? null,
                'contract_amount' => $data['contract_amount'] ?? 0,
                'status' => $data['status'],
                'assigned_at' => now(),
            ],
        ]);

        $vendorUser = User::where('email', $vendor->email)->first();
        if ($vendorUser) {
            $this->notifications->sendToUser(
                $vendorUser,
                'vendor_assignment',
                'Vendor Assignment',
                "You have been assigned to {$event->name}.",
                ['event_id' => $event->id, 'vendor_id' => $vendor->id]
            );
        }

        return back()->with('success', 'Vendor assigned to event.');
    }

    public function updateVendorStatus(Request $request, Event $event, Vendor $vendor): RedirectResponse
    {
        $assigned = $event->vendors()->where('vendors.id', $vendor->id)->exists();
        abort_unless($assigned, 404);

        $user = $request->user();
        $canManage = $user->can('update', $event);
        $isAssignedVendor = $user->isVendor() && strcasecmp((string) $vendor->email, (string) $user->email) === 0;
        abort_unless($canManage || $isAssignedVendor, 403);

        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $event->vendors()->updateExistingPivot($vendor->id, ['status' => $data['status']]);

        return back()->with('success', 'Vendor status updated.');
    }

    public function removeVendor(Event $event, Vendor $vendor): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->vendors()->detach($vendor->id);

        return back()->with('success', 'Vendor removed from event.');
    }

    public function ticket(Event $event)
    {
        $registration = $event->registrations()->where('user_id', auth()->id())->firstOrFail();

        if ($registration->qr_code_path) {
            return response()->file(storage_path('app/public/'.$registration->qr_code_path));
        }

        abort(404);
    }
}

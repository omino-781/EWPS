<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Services\EventService;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function __construct(
        protected EventRepositoryInterface $events,
        protected EventService $eventService,
        protected RegistrationService $registrationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
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

        return response()->json($this->events->paginate($filters));
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $this->eventService->create(array_merge($request->validated(), [
            'organizer_id' => $request->user()->id,
            'status' => $request->input('status', 'draft'),
        ]));

        return response()->json(['message' => 'Event created.', 'data' => $event], 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        return response()->json(['data' => $this->events->find($event->id)]);
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        return response()->json([
            'message' => 'Event updated.',
            'data' => $this->eventService->update($event, $request->validated()),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);
        $this->eventService->delete($event);

        return response()->json(['message' => 'Event deleted.']);
    }

    public function register(Event $event, Request $request): JsonResponse
    {
        $this->authorize('register', $event);

        try {
            $registration = $this->registrationService->register($event, $request->user());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Registered.', 'data' => $registration], 201);
    }

    public function confirmAttendance(Event $event, Request $request): JsonResponse
    {
        abort_unless($request->user()->isParticipant(), 403);

        $registration = $event->registrations()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($registration->status !== 'attended') {
            $registration = $this->registrationService->checkIn($registration);
        }

        return response()->json(['message' => 'Attendance confirmed.', 'data' => $registration]);
    }
}

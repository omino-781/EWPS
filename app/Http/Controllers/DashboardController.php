<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user()->loadMissing('role');

        return match (true) {
            $user->isAdministrator() => $this->adminDashboard(),
            $user->isOrganizer() => $this->organizerDashboard($user),
            $user->isVendor() => $this->vendorDashboard($user),
            default => $this->participantDashboard($user),
        };
    }

    private function adminDashboard(): View
    {
        return view('dashboards.admin', [
            'stats' => $this->statsFromRow(DB::selectOne(<<<'SQL'
                select
                    (select count(*) from events) as events,
                    (select count(*) from users) as users,
                    (select count(*) from venues) as venues,
                    (select count(*) from vendors) as vendors,
                    (select count(*) from event_registrations) as registrations,
                    (select count(*) from payments where status = 'pending') as pending_payments,
                    (select count(*) from tasks where status != 'completed') as open_tasks
            SQL)),
            'recentEvents' => Event::with('venue', 'organizer')->latest()->limit(6)->get(),
        ]);
    }

    private function organizerDashboard(User $user): View
    {
        return view('dashboards.organizer', [
            'stats' => $this->statsFromRow(DB::selectOne(<<<'SQL'
                select
                    (select count(*) from events where organizer_id = ?) as my_events,
                    (select count(*) from event_registrations er join events e on e.id = er.event_id where e.organizer_id = ?) as registrations,
                    (select count(*) from tasks t join events e on e.id = t.event_id where e.organizer_id = ? and t.status != 'completed') as open_tasks,
                    (select count(*) from payments p join events e on e.id = p.event_id where e.organizer_id = ? and p.status = 'pending') as pending_payments
            SQL, [$user->id, $user->id, $user->id, $user->id])),
            'myEvents' => Event::with('venue')->where('organizer_id', $user->id)->latest()->limit(6)->get(),
            'recentRegistrations' => EventRegistration::with('event', 'user')
                ->whereHas('event', fn ($q) => $q->where('organizer_id', $user->id))
                ->latest('registered_at')
                ->limit(5)
                ->get(),
            'openTasks' => Task::with('event')
                ->whereHas('event', fn ($q) => $q->where('organizer_id', $user->id))
                ->where('status', '!=', 'completed')
                ->orderByRaw('deadline is null, deadline asc')
                ->limit(5)
                ->get(),
        ]);
    }

    private function participantDashboard(User $user): View
    {
        return view('dashboards.participant', [
            'stats' => $this->statsFromRow(DB::selectOne(<<<'SQL'
                select
                    (select count(*) from event_registrations where user_id = ?) as my_registrations,
                    (select count(*) from events where status = 'published' and start_date >= ?) as available_events,
                    (select count(*) from event_registrations where user_id = ? and status = 'attended') as attended_events
            SQL, [$user->id, now(), $user->id])),
            'registrations' => $user->registrations()
                ->with('event.category', 'event.venue')
                ->latest('registered_at')
                ->limit(6)
                ->get(),
            'availableEvents' => Event::with('category', 'venue')
                ->where('status', 'published')
                ->where('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(6)
                ->get(),
        ]);
    }

    private function vendorDashboard(User $user): View
    {
        return view('dashboards.vendor', [
            'stats' => $this->statsFromRow(DB::selectOne(<<<'SQL'
                select
                    (select count(*) from event_vendors ev join vendors v on v.id = ev.vendor_id where v.email = ?) as assigned_events,
                    (select count(*) from event_vendors ev join vendors v on v.id = ev.vendor_id where v.email = ? and ev.status != 'completed') as pending_services,
                    (select count(*) from payments p join vendors v on v.id = p.vendor_id where v.email = ? and p.status = 'pending') as pending_payments
            SQL, [$user->email, $user->email, $user->email])),
            'vendorProfile' => Vendor::with('category')->where('email', $user->email)->first(),
            'assignedEvents' => Event::query()
                ->select('events.*', 'event_vendors.status as service_status')
                ->join('event_vendors', 'event_vendors.event_id', '=', 'events.id')
                ->join('vendors', 'vendors.id', '=', 'event_vendors.vendor_id')
                ->with('venue')
                ->where('vendors.email', $user->email)
                ->latest('events.created_at')
                ->limit(6)
                ->get(),
            'payments' => Payment::with('event')
                ->whereHas('vendor', fn ($q) => $q->where('email', $user->email))
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    private function statsFromRow(object $row): array
    {
        return collect((array) $row)
            ->map(fn ($value) => (int) $value)
            ->all();
    }
}

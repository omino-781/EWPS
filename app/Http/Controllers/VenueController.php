<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\VenueRepositoryInterface;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function __construct(
        protected VenueRepositoryInterface $venues,
        protected ActivityLogService $activityLog,
    ) {}

    public function index(): View
    {
        return view('venues.index', ['venues' => $this->venues->paginate()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'capacity' => 'required|integer|min:1',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $venue = $this->venues->create($data);
        $this->activityLog->log('venue.created', $venue);

        return back()->with('success', 'Venue created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $venue = $this->venues->find($id) ?? abort(404);
        $venue = $this->venues->update($venue, $request->validate([
            'name' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]));

        return back()->with('success', 'Venue updated.');
    }
}

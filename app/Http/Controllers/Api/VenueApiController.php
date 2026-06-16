<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\VenueRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueApiController extends Controller
{
    public function __construct(protected VenueRepositoryInterface $venues) {}

    public function index(): JsonResponse
    {
        return response()->json($this->venues->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $venue = $this->venues->create($request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity' => 'required|integer|min:1',
        ]));

        return response()->json(['message' => 'Venue created.', 'data' => $venue], 201);
    }
}

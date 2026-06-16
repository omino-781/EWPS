<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): JsonResponse
    {
        $reports = Report::where('user_id', $request->user()->id)->latest()->paginate(15);

        return response()->json($reports);
    }

    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:'.implode(',', config('epms.report_types')),
            'format' => 'required|in:pdf,excel,csv',
            'event_id' => 'nullable|exists:events,id',
        ]);

        if (in_array($data['type'], ['attendance', 'financial', 'vendor', 'task_progress'], true) && empty($data['event_id'])) {
            return response()->json(['message' => 'Event ID is required for this report type.'], 422);
        }

        $report = $this->reports->generate($request->user(), $data['type'], $data['format'], $data);

        return response()->json(['message' => 'Report generated.', 'data' => $report], 201);
    }
}

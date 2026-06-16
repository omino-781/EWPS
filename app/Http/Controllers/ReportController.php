<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(): View
    {
        return view('reports.index', [
            'reports' => Report::where('user_id', auth()->id())->latest()->paginate(10),
            'events' => Event::orderBy('name')->get(['id', 'name']),
            'types' => config('epms.report_types'),
            'formats' => config('epms.report_formats'),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:'.implode(',', config('epms.report_types')),
            'format' => 'required|in:pdf,excel,csv',
            'event_id' => 'nullable|exists:events,id',
        ]);

        if (in_array($data['type'], ['attendance', 'financial', 'vendor', 'task_progress'], true) && empty($data['event_id'])) {
            return back()->with('error', 'Event ID is required for this report type.');
        }

        $this->reports->generate(auth()->user(), $data['type'], $data['format'], $data);

        return back()->with('success', 'Report generated successfully.');
    }

    public function download(Report $report): StreamedResponse
    {
        abort_unless($report->user_id === auth()->id() || auth()->user()->isAdministrator(), 403);

        $extension = pathinfo($report->file_path, PATHINFO_EXTENSION) ?: $report->format;

        return Storage::disk('public')->download($report->file_path, Str::slug($report->title).'.'.$extension);
    }
}

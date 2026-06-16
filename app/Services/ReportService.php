<?php

namespace App\Services;

use App\Exports\GenericReportExport;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function __construct(protected VenueBookingService $venueBookings) {}

    public function generate(User $user, string $type, string $format, array $parameters = []): Report
    {
        $data = $this->buildDataset($type, $parameters);
        $title = ucfirst(str_replace('_', ' ', $type)).' Report';
        $filename = 'reports/'.Str::uuid().'.'.$this->extension($format);

        match ($format) {
            'pdf' => $this->exportPdf($filename, $title, $data),
            'excel' => $this->exportExcel($filename, $data),
            'csv' => $this->exportCsv($filename, $data),
            default => throw new \InvalidArgumentException('Unsupported format'),
        };

        return Report::create([
            'user_id' => $user->id,
            'event_id' => $parameters['event_id'] ?? null,
            'type' => $type,
            'title' => $title,
            'file_path' => $filename,
            'format' => $format,
            'generated_at' => now(),
            'parameters' => $parameters,
        ]);
    }

    protected function buildDataset(string $type, array $parameters): array
    {
        return match ($type) {
            'event' => $this->eventReport($parameters),
            'attendance' => $this->attendanceReport($parameters),
            'financial' => $this->financialReport($parameters),
            'venue_utilization' => $this->venueReport(),
            'vendor' => $this->vendorReport($parameters),
            'task_progress' => $this->taskReport($parameters),
            default => [],
        };
    }

    protected function eventReport(array $parameters): array
    {
        $events = Event::with(['category', 'venue', 'organizer', 'budgetRecord'])
            ->when($parameters['event_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->get();

        return $events->map(fn ($e) => [
            'ID' => $e->id,
            'Name' => $e->name,
            'Category' => $e->category?->name,
            'Venue' => $e->venue?->name,
            'Start' => $e->start_date->format('Y-m-d H:i'),
            'Status' => $e->status,
            'Budget Allocated' => $e->budgetRecord?->total_amount ?? $e->budget,
            'Budget Spent' => $e->budgetRecord?->spent_amount ?? 0,
            'Budget Remaining' => $e->budgetRecord?->variance() ?? (float) $e->budget,
        ])->toArray();
    }

    protected function attendanceReport(array $parameters): array
    {
        $event = Event::with('registrations.user')->findOrFail($parameters['event_id']);

        return $event->registrations->map(fn ($r) => [
            'Participant' => $r->user->name,
            'Code' => $r->registration_code,
            'Status' => $r->status,
            'Registered' => $r->registered_at?->format('Y-m-d H:i'),
            'Checked In' => $r->checked_in_at?->format('Y-m-d H:i') ?? '-',
        ])->toArray();
    }

    protected function financialReport(array $parameters): array
    {
        $event = Event::with(['expenses', 'payments'])->findOrFail($parameters['event_id']);
        $rows = [];

        foreach ($event->expenses as $expense) {
            $rows[] = [
                'Type' => 'Expense',
                'Description' => $expense->description,
                'Amount' => $expense->amount,
                'Date' => $expense->expense_date->format('Y-m-d'),
            ];
        }

        foreach ($event->payments as $payment) {
            $rows[] = [
                'Type' => 'Payment',
                'Description' => $payment->reference ?? 'Payment',
                'Amount' => $payment->amount,
                'Date' => $payment->paid_at?->format('Y-m-d') ?? '-',
            ];
        }

        return $rows;
    }

    protected function venueReport(): array
    {
        return $this->venueBookings->utilizationReport()->map(function ($bookings, $venueId) {
            $venue = $bookings->first()->venue;

            return [
                'Venue' => $venue->name,
                'Bookings' => $bookings->count(),
                'Capacity' => $venue->capacity,
            ];
        })->values()->toArray();
    }

    protected function vendorReport(array $parameters): array
    {
        $event = Event::with('vendors.category')->findOrFail($parameters['event_id']);

        return $event->vendors->map(fn ($v) => [
            'Vendor' => $v->name,
            'Category' => $v->category?->name,
            'Contract' => $v->pivot->contract_amount,
            'Status' => $v->pivot->status,
        ])->toArray();
    }

    protected function taskReport(array $parameters): array
    {
        $event = Event::with('tasks.assignee')->findOrFail($parameters['event_id']);

        return $event->tasks->map(fn ($t) => [
            'Title' => $t->title,
            'Assignee' => $t->assignee?->name ?? 'Unassigned',
            'Status' => $t->status,
            'Deadline' => $t->deadline?->format('Y-m-d') ?? '-',
        ])->toArray();
    }

    protected function exportPdf(string $path, string $title, array $data): void
    {
        $pdf = Pdf::loadView('reports.pdf', compact('title', 'data'));
        Storage::disk('public')->put($path, $pdf->output());
    }

    protected function exportExcel(string $path, array $data): void
    {
        Excel::store(new GenericReportExport($data), $path, 'public');
    }

    protected function exportCsv(string $path, array $data): void
    {
        $this->exportExcel($path, $data);
    }

    protected function extension(string $format): string
    {
        return match ($format) {
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'csv' => 'csv',
            default => 'txt',
        };
    }
}

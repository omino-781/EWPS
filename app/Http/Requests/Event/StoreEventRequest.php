<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() || $this->user()?->isOrganizer();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:event_categories,id'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'start_date' => ['required', 'date', 'after:now'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,published,ongoing,completed,cancelled'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

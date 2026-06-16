<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event'));
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'category_id' => ['sometimes', 'exists:event_categories,id'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:draft,published,ongoing,completed,cancelled'],
            'max_attendees' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

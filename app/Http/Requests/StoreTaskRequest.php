<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'start_at' => ['nullable', 'date', 'after:now'],
            'end_at' => ['nullable', 'date', 'after:start_at']
        ];
    }
}

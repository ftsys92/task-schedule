<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'working_hours_start' => ['nullable', 'string'],
            'working_hours_end' => ['nullable', 'string'],
        ];
    }
}

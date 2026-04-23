<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveWorkspaceTaskRequest extends FormRequest {
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:pending,in_progress,completed'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}

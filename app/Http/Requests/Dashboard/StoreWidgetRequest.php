<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreWidgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('dashboard.widgets.manage');
    }

    public function rules(): array
    {
        return [
            'widget_key' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:0'],
            'size' => ['required', 'in:small,medium,large,full'],
            'config' => ['nullable', 'array'],
            'is_visible' => ['boolean'],
        ];
    }
}

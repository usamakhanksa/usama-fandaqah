<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('units.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit_number' => ['required', 'string', 'max:50', 'unique:units,unit_number'],
            'unit_category_id' => ['required', 'integer', 'exists:unit_categories,id'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:available,occupied,dirty,clean,maintenance,out_of_order'],
            'description' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'iptv_mac' => ['nullable', 'string', 'max:50'],
            'qr_code' => ['nullable', 'string', 'max:255'],
        ];
    }
}

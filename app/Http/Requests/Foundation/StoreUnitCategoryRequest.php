<?php

namespace App\Http\Requests\Foundation;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name_en' => ['required','string','max:255'],
            'name_ar' => ['required','string','max:255'],
            'code' => ['required','alpha_dash','max:20','unique:unit_categories,code'],
            'default_capacity' => ['required','integer','min:1','max:20'],
            'base_rate_sar' => ['required','numeric','min:0'],
            'active' => ['required','boolean'],
        ];
    }
}

<?php
namespace App\Http\Requests\Foundation;
use Illuminate\Foundation\Http\FormRequest;
class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'unit_category_id' => ['required','exists:unit_categories,id'],
            'name' => ['required','string','max:255'],
            'number' => ['required','string','max:30','unique:units,number'],
            'code' => ['nullable','string','max:30','unique:units,code'],
            'floor_no' => ['nullable','integer','min:0','max:100'],
            'max_occupancy' => ['required','integer','min:1','max:20'],
            'current_rate_sar' => ['required','numeric','min:0'],
            'status' => ['required','in:active,out_of_service,dirty,maintenance'],
        ];
    }
}

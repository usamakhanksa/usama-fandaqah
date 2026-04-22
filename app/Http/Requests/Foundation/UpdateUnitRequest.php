<?php
namespace App\Http\Requests\Foundation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $id = $this->route('unit')?->id;
        return [
            'unit_category_id' => ['required','exists:unit_categories,id'],
            'name' => ['required','string','max:255'],
            'number' => ['required','string','max:30',Rule::unique('units','number')->ignore($id)],
            'code' => ['nullable','string','max:30',Rule::unique('units','code')->ignore($id)],
            'floor_no' => ['nullable','integer','min:0','max:100'],
            'max_occupancy' => ['required','integer','min:1','max:20'],
            'current_rate_sar' => ['required','numeric','min:0'],
            'status' => ['required','in:active,out_of_service,dirty,maintenance'],
        ];
    }
}

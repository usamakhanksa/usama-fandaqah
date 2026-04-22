<?php
namespace App\Http\Requests\Foundation;
use Illuminate\Foundation\Http\FormRequest;
class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'first_name_en' => ['required','string','max:100'],
            'last_name_en' => ['required','string','max:100'],
            'first_name_ar' => ['nullable','string','max:100'],
            'last_name_ar' => ['nullable','string','max:100'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['required','regex:/^\+9665\d{8}$/'],
            'document_type' => ['required','in:saudi_id,iqama,passport'],
            'document_number' => ['required','string','max:30','unique:customers,document_number'],
            'date_of_birth' => ['nullable','date','before:today'],
            'city' => ['required','in:Riyadh,Jeddah,Dammam'],
            'address_line' => ['nullable','string','max:255'],
            'vip' => ['required','boolean'],
            'notes' => ['nullable','string','max:2000'],
        ];
    }
}

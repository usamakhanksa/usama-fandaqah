<?php
namespace App\Http\Requests\Foundation;
use Illuminate\Foundation\Http\FormRequest;
class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'customer_id' => ['required','exists:customers,id'],
            'unit_id' => ['required','exists:units,id'],
            'check_in_date' => ['required','date','after_or_equal:today'],
            'check_out_date' => ['required','date','after:check_in_date'],
            'adults' => ['required','integer','min:1','max:10'],
            'children' => ['nullable','integer','min:0','max:10'],
            'status' => ['required','in:pending,confirmed,checked_in,checked_out,cancelled'],
            'night_rate_sar' => ['required','numeric','min:0'],
            'tax_sar' => ['nullable','numeric','min:0'],
            'source' => ['required','in:direct,phone,walk_in,booking_com,expedia'],
            'special_requests' => ['nullable','string','max:1000'],
        ];
    }
}

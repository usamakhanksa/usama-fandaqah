<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreBookingRequest extends FormRequest {
  public function authorize(): bool { return true; }
  public function rules(): array {
    return [
      'reservation_id'=>['required','integer'],
      'guest_id'=>['required','integer'],
      'room_id'=>['required','integer'],
      'check_in'=>['required','date'],
      'check_out'=>['required','date','after:check_in'],
      'total_amount'=>['required','numeric','min:0']
    ];
  }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update reservations');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $reservationId = $this->route('reservation');
        return [
            'code' => 'sometimes|required|string|max:255|unique:reservations,code,'.$reservationId.',id,deleted_at,NULL',
            'guest_id' => 'sometimes|required|exists:guests,id',
            'room_id' => 'sometimes|required|exists:rooms,id',
            'unit_id' => 'nullable|exists:units,id',
            'status' => 'sometimes|required|string|max:50',
            'reservation_category_type' => 'nullable|string|max:50',
            'special_request' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'shomoos_verification_status' => 'nullable|string|max:50',
            'primary_payment_method' => 'nullable|string|max:30',
            'expected_check_in_time' => 'nullable|date_format:H:i',
            'expected_check_out_time' => 'nullable|date_format:H:i',
            'check_in' => 'sometimes|required|date',
            'check_out' => 'sometimes|required|date|after:check_in',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code' => __('Reservation Code'),
            'guest_id' => __('Guest'),
            'room_id' => __('Room'),
            'unit_id' => __('Unit'),
            'status' => __('Status'),
            'reservation_category_type' => __('Reservation Category Type'),
            'special_request' => __('Special Request'),
            'company_id' => __('Company'),
            'shomoos_verification_status' => __('Shomoos Verification Status'),
            'primary_payment_method' => __('Primary Payment Method'),
            'expected_check_in_time' => __('Expected Check-in Time'),
            'expected_check_out_time' => __('Expected Check-out Time'),
            'check_in' => __('Check-in Date'),
            'check_out' => __('Check-out Date'),
        ];
    }
}
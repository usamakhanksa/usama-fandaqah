<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RoomAdjustmentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('finance.adjustment');
    }

    public function rules()
    {
        return [
            'reservation_id' => 'required|exists:reservations,id',
            'business_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:charge,rebate',
            'reason' => 'required|string|min:5|max:255',
        ];
    }
}

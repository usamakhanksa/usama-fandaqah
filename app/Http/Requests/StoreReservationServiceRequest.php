<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_ar' => [
                Rule::unique('reservation_services')->where(function ($query) {
                    $query->where('name_ar', $this->name_ar)
                       ->where('team_id', $this->team_id)
                       ->whereNull('deleted_at');
                })
            ],
            'name_en' => [
                Rule::unique('reservation_services')->where(function ($query) {
                    $query->where('name_en', $this->name_en)
                       ->where('team_id', $this->team_id)
                       ->whereNull('deleted_at');
                })
            ],
        ];
    }


    public function messages()
    {
        return [
            'name_ar.unique'  => 'Service name ar already recorded',
            'name_en.unique'  => 'Service name en already recorded'
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AddIndividualRequest extends FormRequest
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
            'team_id' => 'required',
            'name' => [
                'required',
                'max:255',
                Rule::unique('companies')->where(function ($query) {
                    $query->where('name', $this->name)
                       ->where('team_id', $this->team_id);
                })
            ],
            'phone' => [
                'required',
                'numeric',
                Rule::unique('companies')->where(function ($query) {
                    $query->where('phone', $this->phone)
                       ->where('team_id', $this->team_id);
                })
            ]
        ];
    }

    public function messages()
    {
        return [
            'team_id.required' => 'team id is required',
            'name.required' => 'Individual name is required',
            'phone.required'  => 'Individual phone is required',
            'phone.unique'  => 'Individual phone must be unique',
            'phone.numeric'  => 'Individual phone must be numeric',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AddCompanyRequest extends FormRequest
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
            ],
            'city' => 'required',
            'address' => 'required',
            'person_incharge_name' => 'required',
            'email' => 'sometimes|nullable|email|unique:companies'
        ];
    }

    public function messages()
    {
        return [
            'team_id.required' => 'team id is required',
            'name.required' => 'company name is required',
            'name.unique'  => 'Company name must be unique',
            'name.max' => 'Company name can not be greater than 255 chars',
            'phone.required'  => 'company phone is required',
            'phone.unique'  => 'Company phone must be unique',
            'phone.numeric'  => 'Company phone must be numeric',
            'city.required'  => 'city is required',
            'address.required'  => 'address is required',
            'person_incharge_name.required'  => 'person in charge name is required',
            'email.email' => 'Email must be valid',
            'email.unique' => 'Email is alreay taken'
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
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
            'name' => 'required|max:255|unique:companies,name,' . $this->id,
            'phone' => 'required|numeric|unique:companies,phone,' . $this->id,
            'city' => 'required',
            'address' => 'required',
            'person_incharge_name' => 'required',
            'email' => 'nullable|email|unique:companies,email,' . $this->id
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'company name is required',
            'name.unique'  => 'company name must be unique',
            'name.max' => 'company name can not be greater than 255 chars',
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

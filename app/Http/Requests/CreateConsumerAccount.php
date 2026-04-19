<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateConsumerAccount extends FormRequest
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
            'name' => 'required|unique:public_api_consumers,name',
            'email' => 'required|email|unique:public_api_consumers,email',
//            'redirect' => 'required',
            'password' => 'required',
            'web_hook_url' => 'required',
            'web_hook_token' => 'required'
        ];
    }

    public function messages()
    {
        return [
          'name.required' => __('Consumer name is required'),
          'name.unique' => __('Consumer name must be unique'),
          'email.required' => __('Consumer email is required'),
          'email.email' => __('Consumer email must be valid email'),
          'email.unique' => __('Consumer email must be unique'),
//          'redirect.required' => __('Redirect url is required'),
          'password.required' => __('Password is required'),
          'web_hook_url.required' => __('Web Hook Url is required'),
          'web_hook_token.required' => __('Web Hook Token is required'),
        ];
    }
}

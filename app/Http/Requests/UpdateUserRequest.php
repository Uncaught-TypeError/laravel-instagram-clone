<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'max:50'],
            'username' => ['sometimes'],
            'password' => ['sometimes'],
            'email' => ['sometimes', 'email'],
            'phone_number' => ['sometimes'],
            'phone_number_verified_at' => ['sometimes'],
            'otp' => ['sometimes'],
            'otp_created_at' => ['sometimes'],
            'website' => ['sometimes', 'url', 'nullable'],
            'bio' => ['sometimes', 'max:150'],
            'gender' => ['sometimes'],
            'avatar' => ['sometimes'],
        ];
    }
}

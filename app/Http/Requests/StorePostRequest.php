<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'caption' => ['required', 'max:255'],
            'image' => ['required', 'array'],
            'image.*' => ['image', 'mimes:jpeg,png,jpg,gif,jfif,webp']
        ];
    }
}

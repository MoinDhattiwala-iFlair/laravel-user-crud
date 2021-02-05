<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:8048'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        if (request()->isMethod('put')) {
            $rules['email'][4] = 'unique:users,email,' . $this->user->id;
            $rules['password'][0] = 'nullable';
        }
        return $rules;
    }
}

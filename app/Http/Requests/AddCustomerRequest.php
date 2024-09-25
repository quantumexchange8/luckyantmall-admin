<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AddCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'regex:/^[a-zA-Z0-9\p{Han}. ]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . User::class],
            'username' => ['required'],
            'country' => ['required'],
            'dial_code' => ['required'],
            'phone' => ['required', 'max:255', 'unique:' . User::class],
            'password' => ['required', Password::defaults(), 'confirmed'],
            'upline' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [
            'name' => trans('public.full_name'),
            'email' => trans('public.email'),
            'username' => trans('public.username'),
            'country' => trans('public.country'),
            'phone' => trans('public.phone_number'),
            'password' => trans('public.password'),
            'upline' => trans('public.upline'),
        ];
    }
}

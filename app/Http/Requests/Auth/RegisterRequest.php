<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Override;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required',
            'name.string' => 'Full name must be a valid string',
            'name.min' => 'Full name must at least be :min characters long',
            'name.max' => 'Full name cannot exceed :max characters',
            
            'email.required' => 'Email is required',
            'email.string' => 'Email must be a valid string',
            'email.max' => 'Email cannot exceed :max characters',
            'email.unique' => 'This email is already registered',

            'password.required' => 'Password is required',
            'password.confirmed' => 'Passwords do not match',
        ];
    }
}

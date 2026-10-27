<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UpdateBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'required', 
                'string', 
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
            ],

            'month' => ['sometimes', 'required', 'string', 'date_format:Y-m'],

            'limit_amount' => ['sometimes', 'bail', 'required', 'decimal:2', 'min:1', 'max:99999999'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'category_id.required' => 'Budget category is required.',
            'category_id.string' => 'Budget category must be a valid string.',
            'category_id.exists' => 'Category provided does not exist in your record.',

            'month.required' => 'Budget month is required.',
            'month.string' => 'Budget month must be a valid string.',
            'month.date_format' => 'Invalid month format.',

            'limit_amount.required' => 'Budget limit amount is required.',
            'limit_amount.decimal' => 'Budget limit amount can have 2 decimal places.',
            'limit_amount.min' => 'Budget limit amount must be greater than 0.',
            'limit_amount.max' => 'Budget limit amount cannot exceed 99,999,99.99',
        ];
    }
}

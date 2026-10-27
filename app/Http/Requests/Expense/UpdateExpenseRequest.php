<?php

namespace App\Http\Requests\Expense;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UpdateExpenseRequest extends FormRequest
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

            'description' => [
                'sometimes', 
                'nullable',  
                'string', 
                'min:10', 
                'max:255'
            ],

            'amount' => ['bail', 'sometimes', 'required', 'decimal:2', 'min:1', 'max:99999999'],
            'expense_date' => ['sometimes', 'required', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'category_id.required' => 'Expense category is required.',
            'category_id.string' => 'Expense must be a valid string.',
            'category_id.exists' => 
                'Category provided does not exists in your record or has been deleted.',
            
            'description.string' => 'Expense description must be a valid string.',
            'description.min' => 'Expense description must at least be :min characters long.',
            'description.max' => 'Expense description cannot exceed :max characters.',

            'amount.required' => 'Expense amount is required.',
            'amount.decimal' => 'Expense amount can have 2 decimal places.',
            'amount.min' => 'Expense amount value must be greater than 0.',
            'amount.max' => 'Expense amount value cannot exceed 999,999,999.99.',
            
            'expense_date.required' => 'Expense date is required.',
            'expense_date.date' => 'Invalid expense date.',

            'notes.string' => 'Notes must be a valid string.',
            'notes.max' => 'Notes cannot exceed :max characters.',
        ];
    }
}

<?php

namespace App\Http\Requests\Budget;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBudgetRequest extends FormRequest
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
            'month' => ['sometimes', 'string', 'date_format:Y-m'],
            
            'category_id' => [
                'sometimes', 
                'string', 
                Rule::exists('categories', 'id')
                    ->where('user_id', $this->user()->id)
            ],

            'page' => ['sometimes', 'integer', 'min:1'],

            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

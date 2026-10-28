<?php

namespace App\Http\Requests\Summary;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class IndexSummaryRequest extends FormRequest
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
            'group_by' => ['sometimes', 'string', 'in:category,date'],
            'month' => ['sometimes', 'string', 'date_format:Y-m'],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $data = $validator->getData();

            if (isset($data['month']) && (isset($data['from']) || isset($data['to']))) {
                $validator->errors()->add(
                    'month',
                    'The month parameter cannot be combined with from/to.'
                );
            }

            if (isset($data['from'], $data['to']) && $data['from'] > $data['to']) {
                $validator->errors()->add(
                    'to',
                    'The to date must be on or after the from date.'
                );
            }
        });
    }
}

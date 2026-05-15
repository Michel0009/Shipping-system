<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class ContractTermFormRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
      return match($this->route()->getActionMethod()) {

        'create_contract_term' => [
            'order' => 'required|numeric',
            'term_text' => ['required', 'string', new NoHtml],
        ],

        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'create_contract_term' => [
                'order.required' => 'ترتيب البند مطلوب',
                'order.numeric' => 'ترتيب البند يجب أن يكون رقماً',

                'term_text.required' => 'البند مطلوب',
                'term_text.string' => 'البند يجب أن يكون نصاً',
            ],

            default => [],
        };
    }

}

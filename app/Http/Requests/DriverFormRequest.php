<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
      return match($this->route()->getActionMethod()) {
        'attach_governorate' => [
           'gov_id' => 'required|exists:governorates,id'
        ],
        'detatch_governorate' => [
           'gov_id' => 'required|exists:governorates,id'
        ],

        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'attach_governorate' => [
                'gov_id.required' => 'يجب تحديد المحافظة',
                'gov_id.exists' => 'المحافظة غير موجودة',
            ],
            'detatch_governorate' => [
                'gov_id.required' => 'يجب تحديد المحافظة',
                'gov_id.exists' => 'المحافظة غير موجودة',
            ],

            default => [],
        };
    }
}

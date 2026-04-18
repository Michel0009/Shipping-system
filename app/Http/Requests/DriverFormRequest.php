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
        'set_driver_location' => [
           'lat' => 'required|numeric',
           'lng' => 'required|numeric'
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
            'set_driver_location' => [
                'lat.required' => 'إحداثيات خط العرض مطلوبة',
                'lat.numeric' => 'خط العرض يجب أن يكون رقماً',

                'lng.required' => 'إحداثيات خط الطول مطلوبة',
                'lng.numeric' => 'خط الطول يجب أن يكون رقماً',
            ],

            default => [],
        };
    }
}

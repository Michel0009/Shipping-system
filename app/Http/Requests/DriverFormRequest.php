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
    protected function prepareForValidation()
    {
        if ($this->route()->getActionMethod() === 'get_driver_details_for_admin') {
            $this->merge([
                'id' => $this->route('id'),
            ]);
        }
    }
    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'attach_governorate' => [
                'gov_id' => 'required|exists:governorates,id'
            ],
            'detatch_governorate' => [
                'gov_id' => 'required|exists:governorates,id'
            ],
            'search_for_driver' => [
                'driver_number'  => 'required|string|exists:users,user_number',
            ],
            'get_driver_details_for_admin' => [
                'id' => 'required|integer|exists:drivers,id',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return match ($this->route()->getActionMethod()) {

            'attach_governorate' => [
                'gov_id.required' => 'يجب تحديد المحافظة',
                'gov_id.exists' => 'المحافظة غير موجودة',
            ],
            'detatch_governorate' => [
                'gov_id.required' => 'يجب تحديد المحافظة',
                'gov_id.exists' => 'المحافظة غير موجودة',
            ],
            'search_for_driver' => [
                'driver_number.required' => 'يرجى إدخال رقم السائق',
                'driver_number.string'   => 'يجب أن يكون رقم السائق نصاً صحيحاً',
                'driver_number.exists'   => 'رقم السائق المدخل غير موجود في النظام',
            ],
            'get_driver_details_for_admin' => [
                'id.required' => 'معرف السائق مطلوب',
                'id.integer'  => 'يجب أن يكون المعرف رقماً صحيحاً',
                'id.exists'   => 'السائق المطلوب غير موجود في النظام',
            ],
            default => [],
        };
    }
}

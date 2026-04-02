<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class VehicleTypeFormRequest extends FormRequest
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

        'create_vehicle_type' => [
            'type' => ['required', 'string', 'max:255', new NoHtml],
            'description' => ['required', 'string', new NoHtml],
            'vehicle_coefficient' => 'required|numeric|gt:0',
            'avg_fuel_consumption' => 'required|numeric|min:0',
            'base_fare' => 'required|numeric|gt:0',
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0',
            'min_length' => 'required|numeric|min:0',
            'max_length' => 'required|numeric|min:0',
            'min_width' => 'required|numeric|min:0',
            'max_width' => 'required|numeric|min:0',
            'min_height' => 'required|numeric|min:0',
            'max_height' => 'required|numeric|min:0',
        ],
        'update_vehicle_type' => [
            'type' => ['sometimes', 'string', 'max:255', new NoHtml],
            'description' => ['sometimes', 'string', new NoHtml],
            'vehicle_coefficient' => 'sometimes|numeric|gt:0',
            'avg_fuel_consumption' => 'sometimes|numeric|min:0',
            'base_fare' => 'sometimes|numeric|gt:0',
            'min_weight' => 'sometimes|numeric|min:0',
            'max_weight' => 'sometimes|numeric|min:0',
            'min_length' => 'sometimes|numeric|min:0',
            'max_length' => 'sometimes|numeric|min:0',
            'min_width' => 'sometimes|numeric|min:0',
            'max_width' => 'sometimes|numeric|min:0',
            'min_height' => 'sometimes|numeric|min:0',
            'max_height' => 'sometimes|numeric|min:0',
        ],

        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'create_vehicle_type' => [
                'required' => '.حقل :attribute مطلوب',
                'string'    => '.حقل :attribute يجب أن يكون نصاً',
                'max' => '.حقل :attribute يجب ألا يتجاوز :max حرفاً',
                'numeric'    => '.حقل :attribute يجب أن يكون رقماً',
                'gt' => '.حقل :attribute يجب أن يكون أكبر من 0',
                'min' => '.حقل :attribute يجب أن يكون أكبر أو يساوي ال 0',
            ],
            'update_vehicle_type' => [
                'string'    => '.حقل :attribute يجب أن يكون نصاً',
                'max' => '.حقل :attribute يجب ألا يتجاوز :max حرفاً',
                'numeric'    => '.حقل :attribute يجب أن يكون رقماً',
                'gt' => '.حقل :attribute يجب أن يكون أكبر من 0',
                'min' => '.حقل :attribute يجب أن يكون أكبر أو يساوي ال 0',
            ],

            default => [],
        };
    }

    public function attributes(): array
    {
        return match ($this->route()->getActionMethod()) {
            'create_vehicle_type' => [
                'type' => 'النوع',
                'description' => 'الوصف',
                'vehicle_coefficient' => 'معامل المركبة',
                'avg_fuel_consumption' => 'متوسط استهلاك الوقود',
                'base_fare' => 'سعر الانطلاق',
                'min_weight' => '(أقل وزن ممكن تحمله)',
                'max_weight' => '(أعلى وزن ممكن تحمله)',
                'min_length' => '(الحد الأدنى لطول المركبة)',
                'max_length' => '(الحد الأعلى لطول المركبة)',
                'min_width' => '(الحد الأدنى لعرض المركبة)',
                'max_width' => '(الحد الأعلى لعرض المركبة)',
                'min_height' => '(الحد الأدنى لارتفاع المركبة)',
                'max_height' => '(الحد الأعلى لارتفاع المركبة)'

            ],
            'update_vehicle_type' => [
                'type' => 'النوع',
                'description' => 'الوصف',
                'vehicle_coefficient' => 'معامل المركبة',
                'avg_fuel_consumption' => 'متوسط استهلاك الوقود',
                'base_fare' => 'سعر الانطلاق',
                'min_weight' => '(أقل وزن ممكن تحمله)',
                'max_weight' => '(أعلى وزن ممكن تحمله)',
                'min_length' => '(الحد الأدنى لطول المركبة)',
                'max_length' => '(الحد الأعلى لطول المركبة)',
                'min_width' => '(الحد الأدنى لعرض المركبة)',
                'max_width' => '(الحد الأعلى لعرض المركبة)',
                'min_height' => '(الحد الأدنى لارتفاع المركبة)',
                'max_height' => '(الحد الأعلى لارتفاع المركبة)'
            ],

            default => [],
        };
    }


}

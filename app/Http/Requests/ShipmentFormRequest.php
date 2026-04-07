<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class ShipmentFormRequest extends FormRequest
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
        'create_shipment' => [
            'weight' => 'required|numeric|gt:0',
            'height' => 'required|numeric|gt:0',
            'width' => 'required|numeric|gt:0',
            'length' => 'required|numeric|gt:0',
            'object' => ['required', 'string', 'max:255', new NoHtml],
            'insurance' => 'required|boolean',
            'start_position_lat' => 'required|numeric',
            'start_position_lng' => 'required|numeric',
            'end_position_lat' => 'required|numeric',
            'end_position_lng' => 'required|numeric',
            'start_governorate_id' => 'required|exists:governorates,id',
            'end_governorate_id' => 'required|exists:governorates,id'
        ],
        'update_shipment' => [
            'weight' => 'sometimes|numeric|gt:0',
            'height' => 'sometimes|numeric|gt:0',
            'width' => 'sometimes|numeric|gt:0',
            'length' => 'sometimes|numeric|gt:0',
            'object' => ['sometimes', 'string', 'max:255', new NoHtml],
            'insurance' => 'sometimes|boolean',
            'start_position_lat' => 'sometimes|numeric',
            'start_position_lng' => 'sometimes|numeric',
            'end_position_lat' => 'sometimes|numeric',
            'end_position_lng' => 'sometimes|numeric',
            'start_governorate_id' => 'sometimes|exists:governorates,id',
            'end_governorate_id' => 'sometimes|exists:governorates,id',
        ],
        'send_to_driver' => [
            'driver_id' => 'required|exists:drivers,id',
            'price' => 'required|numeric',
            'distanceToStart' => 'required|numeric',
            'shipmentDistance' => 'required|numeric',
        ],
        'respond_to_request' => [
            'user_id' => 'required|exists:users,id',
            'action' => 'required|boolean'
        ],
        
        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'create_shipment' => [
                'weight.required' => 'حقل الوزن مطلوب',
                'weight.numeric' => 'يجب أن يكون الوزن رقماً',
                'weight.gt' => 'يجب أن يكون الوزن أكبر من 0',

                'height.required' => 'حقل الارتفاع مطلوب',
                'height.numeric' => 'يجب أن يكون الارتفاع رقماً',
                'height.gt' => 'يجب أن يكون الارتفاع أكبر من 0',

                'width.required' => 'حقل العرض مطلوب',
                'width.numeric' => 'يجب أن يكون العرض رقماً',
                'width.gt' => 'يجب أن يكون العرض أكبر من 0',

                'length.required' => 'حقل الطول مطلوب',
                'length.numeric' => 'يجب أن يكون الطول رقماً',
                'length.gt' => 'يجب أن يكون الطول أكبر من 0',

                'object.required' => 'وصف الشحنة مطلوب',
                'object.string' => 'وصف الشحنة يجب أن يكون نصاً',
                'object.max' => 'وصف الشحنة يجب ألا يتجاوز 255 حرفاً',

                'insurance.required' => 'حقل التأمين مطلوب',
                'insurance.boolean' => 'قيمة التأمين يجب أن تكون صحيحة أو خاطئة',

                'start_position_lat.required' => 'إحداثيات خط العرض لنقطة البداية مطلوبة',
                'start_position_lat.numeric' => 'خط العرض لنقطة البداية يجب أن يكون رقماً',

                'start_position_lng.required' => 'إحداثيات خط الطول لنقطة البداية مطلوبة',
                'start_position_lng.numeric' => 'خط الطول لنقطة البداية يجب أن يكون رقماً',

                'end_position_lat.required' => 'إحداثيات خط العرض لنقطة النهاية مطلوبة',
                'end_position_lat.numeric' => 'خط العرض لنقطة النهاية يجب أن يكون رقماً',

                'end_position_lng.required' => 'إحداثيات خط الطول لنقطة النهاية مطلوبة',
                'end_position_lng.numeric' => 'خط الطول لنقطة النهاية يجب أن يكون رقماً',

                'start_governorate_id.required' => 'محافظة نقطة البداية مطلوبة',
                'start_governorate_id.exists' => 'محافظة نقطة البداية غير موجودة',

                'end_governorate_id.required' => 'محافظة نقطة النهاية مطلوبة',
                'end_governorate_id.exists' => 'محافظة نقطة النهاية غير موجودة',
            ],
            'update_shipment' => [
                'weight.gt' => 'يجب أن يكون الوزن أكبر من 0',
                'height.gt' => 'يجب أن يكون الارتفاع أكبر من 0',
                'width.gt' => 'يجب أن يكون العرض أكبر من 0',
                'length.gt' => 'يجب أن يكون الطول أكبر من 0',

                'object.max' => 'وصف الشحنة يجب ألا يتجاوز 255 حرفاً',

                'insurance.boolean' => 'قيمة التأمين يجب أن تكون صحيحة أو خاطئة',

                'start_position_lat.numeric' => 'خط العرض لنقطة البداية يجب أن يكون رقماً',
                'start_position_lng.numeric' => 'خط الطول لنقطة البداية يجب أن يكون رقماً',
                'end_position_lat.numeric' => 'خط العرض لنقطة النهاية يجب أن يكون رقماً',
                'end_position_lng.numeric' => 'خط الطول لنقطة النهاية يجب أن يكون رقماً',

                'start_governorate_id.exists' => 'محافظة نقطة البداية غير موجودة',
                'end_governorate_id.exists' => 'محافظة نقطة النهاية غير موجودة',
            ],
            'send_to_driver' => [
                'driver_id.required' => 'حقل رقم السائق مطلوب',
                'driver_id.exists' => 'رقم السائق غير موجود',

                'price.required' => 'حقل السعر مطلوب',
                'price.numeric' => 'السعر يجب أن يكون رقماً',

                'distanceToStart.required' => 'حقل المسافة للبدء مطلوب',
                'distanceToStart.numeric' => 'المسافة للبدء يجب أن تكون رقماً',

                'shipmentDistance.required' => 'حقل مسافة الشحنة مطلوب',
                'shipmentDistance.numeric' => 'مسافة الشحنة يجب أن تكون رقماً',
            ],
            'respond_to_request' => [
                'user_id.required' => 'حقل رقم المستخدم مطلوب',
                'user_id.exists' => 'المستخدم غير موجود',

                'action.required' => 'حقل الإجراء مطلوب',
                'action.boolean' => 'قيمة الإجراء يجب أن تكون 0 أو 1',
            ],

            default => [],
        };
    }

}

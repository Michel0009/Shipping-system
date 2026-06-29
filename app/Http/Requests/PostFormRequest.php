<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class PostFormRequest extends FormRequest
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
        return match ($this->route()->getActionMethod()) {
            'create_post' => [
                'weight' => 'required|numeric|gt:0',
                'height' => 'required|numeric|gt:0',
                'width' => 'required|numeric|gt:0',
                'length' => 'required|numeric|gt:0',
                'object' => ['required', 'string', 'max:255', new NoHtml],
                'start_position_lat' => 'required|numeric',
                'start_position_lng' => 'required|numeric',
                'end_position_lat' => 'required|numeric',
                'end_position_lng' => 'required|numeric',
                'start_governorate_id' => 'required|exists:governorates,id',
                'end_governorate_id' => 'required|exists:governorates,id',
                'start_location_details' => ['required', 'string', 'max:500', new NoHtml],
                'end_location_details' => ['required', 'string', 'max:500', new NoHtml],
                'last_date' => 'required|date|after_or_equal:today|before_or_equal:+6 months',
            ],
            'update_prices' => [
                'post_id' => 'required|exists:posts,id',
                'min_price' => 'required|numeric|gt:0',
                'max_price' => 'required|numeric|gt:min_price',
            ],
            'apply_post' => [
                'post_id' => 'required|exists:posts,id',
                'price' => 'required|numeric|gt:0',
                'date' => 'required|date|after_or_equal:today',
            ],
            'choose_driver_for_post' => [
                'post_id'   => 'required|exists:posts,id',
                'driver_id' => 'required|exists:drivers,id',
            ],
            default => [],
        };
    }

    public function messages(): array
    {
        return [
            'last_date.after_or_equal' => 'تاريخ انتهاء الإعلان يجب أن يكون تاريخاً مستقبلياً.',
            'last_date.before_or_equal'=> 'تاريخ انتهاء الإعلان لا يمكن أن يتجاوز 6 أشهر من تاريخ اليوم.',
            'date.after_or_equal' => 'تاريخ التسليم يجب أن يكون تاريخاً مستقبلياً.',
            'max_price.gt' => 'يجب أن يكون الحد الأعلى للسعر أكبر من الحد الأدنى.',
            'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
            'required' => 'حقل :attribute مطلوب.',
            'gt' => 'حقل :attribute يجب أن يكون أكبر من الصفر.',
            'exists' => 'القيمة المختارة لـ :attribute غير صالحة.',
            'date' => 'حقل :attribute ليس تاريخاً صحيحاً.',
        ];
    }

    public function attributes(): array
    {
        return [
            'weight' => 'الوزن',
            'height' => 'الارتفاع',
            'width' => 'العرض',
            'length' => 'الطول',
            'object' => 'وصف الشحنة',
            'start_governorate_id' => 'محافظة البداية',
            'end_governorate_id' => 'محافظة النهاية',
            'start_location_details' => 'تفاصيل موقع البداية',
            'end_location_details' => 'تفاصيل موقع النهاية',
            'start_position_lat' => 'إحداثيات خط العرض لنقطة البداية',
            'start_position_lng' => 'إحداثيات خط الطول لنقطة البداية',
            'end_position_lat' => 'إحداثيات خط العرض لنقطة النهاية',
            'end_position_lng' => 'إحداثيات خط الطول لنقطة النهاية',
            'last_date' => 'تاريخ النهاية',
            'min_price' => 'الحد الأدنى للسعر',
            'post_id' => 'معرف الإعلان',
            'price' => 'السعر',
            'date' => 'التاريخ',
            'driver_id' => 'معرف السائق',
        ];
    }
}

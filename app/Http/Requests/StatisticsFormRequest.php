<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatisticsFormRequest extends FormRequest
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
        return [
            'filter_date'       => 'required|string|in:days,months,years',
            'start_date'        => 'sometimes|nullable|date|before_or_equal:today',
            'end_date'          => 'sometimes|nullable|date|after_or_equal:start_date|before_or_equal:today',
            'governorate_ids'   => 'sometimes|nullable|array',
            'governorate_ids.*' => 'integer|exists:governorates,id',
        ];
    }
    public function attributes(): array
    {
        return [
            'filter_date'       => 'نوع الفلترة الزمني',
            'start_date'        => 'تاريخ البداية',
            'end_date'          => 'تاريخ النهاية',
            'governorate_ids'   => 'المحافظات',
            'governorate_ids.*' => 'المحافظة المحددة',
        ];
    }

    public function messages(): array
    {
        return [
            'filter_date.required' => 'حقل :attribute مطلوب ولا يمكن تركه فارغاً.',
            'filter_date.string'   => 'يجب أن يكون حقل :attribute نصاً.',
            'filter_date.in'       => 'القيمة المحددة في :attribute غير صحيحة. يجب أن تكون إما أيام (days)، أشهر (months)، أو سنوات (years).',

            'start_date.date'      => 'حقل :attribute يجب أن يكون بصيغة تاريخ صحيحة.',
            'start_date.before_or_equal'   => 'يجب أن يكون حقل :attribute مساوياً لليوم أو سابقاً له، لا يمكن اختيار تاريخ مستقبلي.',

            'end_date.date'        => 'حقل :attribute يجب أن يكون بصيغة تاريخ صحيحة.',
            'end_date.after_or_equal' => 'يجب أن يكون :attribute مساوياً أو لاحقاً لـ تاريخ البداية.',
            'end_date.before_or_equal'     => 'يجب أن يكون حقل :attribute مساوياً لليوم أو سابقاً له، لا يمكن اختيار تاريخ مستقبلي.',
            
            'governorate_ids.array'   => 'حقل :attribute يجب أن يكون مصفوفة (Array).',
            'governorate_ids.*.integer' => 'يجب أن يكون معرّف :attribute رقماً صحيحاً.',
            'governorate_ids.*.exists'  => 'القيمة المختارة لـ :attribute غير موجودة.',
        ];
    }

}

<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class UserFormRequest extends FormRequest
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
        $noHtml = new NoHtml();
        $fileLimit = 'max:5120';
        return match ($this->route()->getActionMethod()) {
            'create_driver' => [
                'first_name'   => ['required', 'string', 'max:255', $noHtml],
                'last_name'    => ['required', 'string', 'max:255', $noHtml],
                'email'        => ['required', 'email', 'unique:users,email'],
                'phone_number' => ['required', 'string', 'max:20', $noHtml],
                'location'     => ['required', 'string', $noHtml],
                'father_name'      => ['required', 'string', 'max:255', $noHtml],
                'mother_name'      => ['required', 'string', 'max:255', $noHtml],
                'mother_last_name' => ['required', 'string', 'max:255', $noHtml],
                'birth_date'       => ['required', 'date', 'before:today'],
                'birth_place'      => ['required', 'string', $noHtml],
                'national_number'  => ['required', 'string', $noHtml],
                'governorate'      => ['required', 'string', $noHtml],
                'city'             => ['required', 'string', $noHtml],
                'neighborhood'     => ['required', 'string', $noHtml],
                'gender' => ['required', 'string', 'in:ذكر,أنثى', $noHtml],
                'nationality'      => ['required', 'string', $noHtml],
                'additional_phone_number' => ['nullable', 'string', 'max:20', $noHtml],
                'personal_picture' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', $fileLimit],
                'license_file'     => ['required', 'file', 'mimes:pdf', $fileLimit],
                'unconvicted_file' => ['required', 'file', 'mimes:pdf', $fileLimit],
                'governorate_ids'   => ['required', 'array', 'min:1'],
                'governorate_ids.*' => ['exists:governorates,id'],
                'vehicle_type_id'      => ['required', 'exists:vehicle_types,id'],
                'license_plate_number' => ['required', 'string', $noHtml],
                'manufacturer'         => ['required', 'string', $noHtml],
                'model'                => ['required', 'string', $noHtml],
                'year_of_manufacture'  => ['required', 'integer', 'min:1900'],
                'color'                => ['required', 'string', $noHtml],
                'fuel_type'            => ['required', 'string', $noHtml],
                'car_status'           => ['required', 'string', $noHtml],
                'car_papers'            => ['required', 'array', 'min:1'],
                'car_papers.*.type'     => ['required', 'string', $noHtml],
                'car_papers.*.car_file' => ['required', 'file', 'mimes:pdf', $fileLimit],
            ],

            default => [],
        };
    }
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return match ($this->route()->getActionMethod()) {
            'create_driver' => [
                'required' => '.حقل :attribute مطلوب',
                'string'   => '.حقل :attribute يجب أن يكون نصاً',
                'max'      => '.حقل :attribute يجب ألا يتجاوز :max حرفاً',
                'max.file' => '.حقل :attribute يجب ألا يتجاوز :max كيلوبايت',
                'email'    => '.البريد الإلكتروني المدخل غير صحيح',
                'unique'   => '.هذا :attribute مستخدم بالفعل',
                'date'     => '.تاريخ :attribute غير صحيح',
                'before'   => '.يجب أن يكون :attribute تاريخاً سابقاً لليوم',
                'in'       => '.القيمة المختارة لـ :attribute غير صالحة',
                'exists'   => '.القيمة المختارة لـ :attribute غير موجودة',
                'file'     => '.يجب رفع ملف في حقل :attribute',
                'mimes'    => '.يجب أن يكون الملف بصيغة: :values',
                'integer'  => '.يجب أن يكون :attribute رقماً',
                'min'      => '.يجب أن يكون :attribute على الأقل :min',
            ],

            default => [],
        };
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return match ($this->route()->getActionMethod()) {
            'create_driver' => [
                'first_name'           => 'الاسم الأول',
                'last_name'            => 'الكنية',
                'email'                => 'البريد الإلكتروني',
                'phone_number'         => 'رقم الهاتف',
                'location'             => 'الموقع',
                'father_name'          => 'اسم الأب',
                'mother_name'          => 'اسم الأم',
                'mother_last_name'     => 'كنية الأم',
                'birth_date'           => 'تاريخ الميلاد',
                'birth_place'          => 'مكان الميلاد',
                'national_number'      => 'الرقم الوطني',
                'governorate'          => 'المحافظة',
                'city'                 => 'المدينة',
                'neighborhood'         => 'الحي',
                'gender'               => 'الجنس',
                'nationality'          => 'الجنسية',
                'personal_picture'     => 'الصورة الشخصية',
                'license_file'         => 'ملف الرخصة',
                'unconvicted_file'     => 'ورقة غير محكوم',
                'governorate_ids'      => 'المحافظات',
                'vehicle_type_id'      => 'نوع المركبة',
                'license_plate_number' => 'رقم اللوحة',
                'manufacturer'         => 'الشركة المصنعة',
                'model'                => 'الموديل',
                'year_of_manufacture'  => 'سنة الصنع',
                'color'                => 'اللون',
                'fuel_type'            => 'نوع الوقود',
                'car_status'           => 'حالة السيارة',
                'car_papers'           => 'أوراق السيارة',
            ],

            default => [],
        };
    }
}

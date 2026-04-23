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
        $fileLimit = 'max:10240';
        return match ($this->route()->getActionMethod()) {
            'create_driver' => [
                'first_name'   => ['required', 'string', 'max:255', $noHtml],
                'last_name'    => ['required', 'string', 'max:255', $noHtml],
                'email'        => ['required', 'email', 'unique:users,email', 'max:255', $noHtml],
                'phone_number' => ['required', 'string', 'max:20', $noHtml],
                'father_name'      => ['required', 'string', 'max:255', $noHtml],
                'mother_name'      => ['required', 'string', 'max:255', $noHtml],
                'mother_last_name' => ['required', 'string', 'max:255', $noHtml],
                'birth_date'       => ['required', 'date', 'before:today'],
                'birth_place'      => ['required', 'string', 'max:255', $noHtml],
                'national_number'  => ['required', 'string', 'max:50', $noHtml],
                'governorate'      => ['required', 'string', 'max:100', $noHtml],
                'city'             => ['required', 'string', 'max:100', $noHtml],
                'neighborhood'     => ['required', 'string', 'max:100', $noHtml],
                'gender'           => ['required', 'string', 'in:ذكر,أنثى', $noHtml],
                'nationality'      => ['required', 'string', 'max:255', $noHtml],
                'additional_phone_number' => ['nullable', 'string', 'max:20', $noHtml],
                'personal_picture' => ['required', 'file', 'mimes:jpeg,jpg,png,heic,heif', $fileLimit],
                'license_file'     => ['required', 'file', 'mimes:pdf', $fileLimit],
                'unconvicted_file' => ['required', 'file', 'mimes:pdf', $fileLimit],
                'governorate_ids'   => ['required', 'array', 'min:1', 'max:50'],
                'governorate_ids.*' => ['exists:governorates,id'],
                'vehicle_type_id'      => ['required', 'exists:vehicle_types,id'],
                'license_plate_number' => ['required', 'string', 'max:30', $noHtml],
                'manufacturer'         => ['required', 'string', 'max:100', $noHtml],
                'model'                => ['required', 'string', 'max:100', $noHtml],
                'year_of_manufacture'  => ['required', 'integer', 'min:1900', 'max:' . date('Y')],
                'color'                => ['required', 'string', 'max:50', $noHtml],
                'fuel_type'            => ['required', 'string', 'max:50', $noHtml],
                'car_status'           => ['required', 'string', 'max:100', $noHtml],
                'car_papers'            => ['required', 'array', 'min:1', 'max:10'],
                'car_papers.*.type'     => ['required', 'string', 'max:100', 'in:اجار,ميكانيك,ملكية', $noHtml],
                'car_papers.*.car_file' => ['required', 'file', 'mimes:pdf', $fileLimit],
            ],
            'edit_profile' => $this->edit_profile_rules(),
            'save_device_token' => [
                'token' => 'required|string'
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
                'string'    => '.حقل :attribute يجب أن يكون نصاً',
                'max.string' => '.حقل :attribute يجب ألا يتجاوز :max حرفاً',
                'personal_picture.max' => '.حقل :attribute يجب ألا يتجاوز :max كيلوبايت',
                'license_file.max'     => '.حقل :attribute يجب ألا يتجاوز :max كيلوبايت',
                'unconvicted_file.max' => '.حقل :attribute يجب ألا يتجاوز :max كيلوبايت',
                'car_papers.*.car_file.max' => '.حقل :attribute يجب ألا يتجاوز :max كيلوبايت',
                'year_of_manufacture.max' => '.حقل :attribute يجب ألا يتجاوز عام :max',
                'email'    => '.البريد الإلكتروني المدخل غير صحيح',
                'unique'   => '.هذا :attribute مستخدم بالفعل',
                'date'     => '.تاريخ :attribute غير صحيح',
                'before'   => '.يجب أن يكون :attribute تاريخاً سابقاً لليوم',
                'in'       => '.القيمة المختارة لـ :attribute غير صالحة',
                'exists'   => '.القيمة المختارة لـ :attribute غير موجودة',
                'file'     => '.يجب رفع ملف في حقل :attribute',
                'mimes'    => '.:values يجب أن يكون الملف بصيغة',
                'integer'  => '.يجب أن يكون :attribute رقماً',
                'min'      => '.يجب أن يكون :attribute على الأقل :min',
                'min.numeric' => '.يجب أن يكون :attribute :min عام أو أحدث'
            ],
            'edit_profile' => [
                'required' => '.حقل :attribute مطلوب',
                'string' => '.حقل :attribute يجب أن يكون نصاً',
                'max' => '.حقل :attribute يجب ألا يتجاوز :max عنصر',
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
            'edit_profile' => [
                'first_name' => 'الاسم الأول',
                'last_name' => 'الكنية',
                'phone_number' => 'رقم الهاتف',
            ],

            default => [],
        };
    }

    private function edit_profile_rules(): array
    {
        $user = auth()->user();
        $noHtml = new NoHtml();

        if ($user->role_id == 4) {
            return [
                'phone_number' => ['required', 'string', 'max:20', $noHtml],
            ];
        }
        return [
            'first_name'   => ['sometimes', 'string', 'max:255', $noHtml],
            'last_name'    => ['sometimes', 'string', 'max:255', $noHtml],
            'phone_number' => ['sometimes', 'string', 'max:20', $noHtml],
        ];
    }
}

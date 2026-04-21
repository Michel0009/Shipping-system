<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
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
        $method = $this->route()->getActionMethod();

        if (in_array($method, ['get_driver_details_for_admin', 'update_driver'])) {
            $this->merge([
                'id' => $this->route('id'),
            ]);
        }
    }
    public function rules(): array
    {
        $noHtml = new NoHtml();
        $fileLimit = 'max:10240';
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

            'search_for_driver' => [
                'driver_number'  => 'required|string|exists:users,user_number',
            ],
            'get_driver_details_for_admin' => [
                'id' => 'required|integer|exists:drivers,id',
            ],
            'update_driver' => [
                'id'                        => ['required', 'exists:drivers,id'],
                'first_name'                => ['sometimes', 'string', 'max:255', $noHtml],
                'last_name'                 => ['sometimes', 'string', 'max:255', $noHtml],
                'phone_number'              => ['sometimes', 'string', 'max:20', $noHtml],
                'father_name'               => ['sometimes', 'string', 'max:255', $noHtml],
                'mother_name'               => ['sometimes', 'string', 'max:255', $noHtml],
                'mother_last_name'          => ['sometimes', 'string', 'max:255', $noHtml],
                'birth_date'                => ['sometimes', 'date', 'before:today'],
                'birth_place'               => ['sometimes', 'string', 'max:255', $noHtml],
                'national_number'           => ['sometimes', 'string', 'max:50', $noHtml],
                'governorate'               => ['sometimes', 'string', 'max:100', $noHtml],
                'city'                      => ['sometimes', 'string', 'max:100', $noHtml],
                'neighborhood'              => ['sometimes', 'string', 'max:100', $noHtml],
                'gender'                    => ['sometimes', 'string', 'in:ذكر,أنثى', $noHtml],
                'additional_phone_number'   => ['nullable', 'string', 'max:20', $noHtml],
                'nationality'               => ['sometimes', 'string', 'max:255', $noHtml],
                'vehicle_type_id'           => ['sometimes', 'exists:vehicle_types,id'],
                'license_plate_number'      => ['sometimes', 'string', 'max:30', $noHtml],
                'manufacturer'              => ['sometimes', 'string', 'max:100', $noHtml],
                'model'                     => ['sometimes', 'string', 'max:100', $noHtml],
                'year_of_manufacture'       => ['sometimes', 'integer', 'min:1900', 'max:' . date('Y')],
                'color'                     => ['sometimes', 'string', 'max:50', $noHtml],
                'fuel_type'                 => ['sometimes', 'string', 'max:50', $noHtml],
                'car_status'                => ['sometimes', 'string', 'max:100', $noHtml],
                'unconvicted_file'          => ['sometimes',  'file', 'mimes:pdf', $fileLimit],
                'license_file'              => ['sometimes',  'file', 'mimes:pdf', $fileLimit],
                'personal_picture'          => ['sometimes', 'file', 'mimes:jpeg,jpg,png,pdf,heic,heif', $fileLimit],
                'car_papers'                => ['sometimes', 'array', 'min:1', 'max:10'],
                'car_papers.*.type'         => ['required_with:car_papers', 'string', 'max:100', 'in:اجار,ميكانيك,ملكية', $noHtml],
                'car_papers.*.car_file'     => ['required_with:car_papers', 'file', 'mimes:pdf', $fileLimit],
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
            'update_driver' => [
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
                'min.numeric' => '.يجب أن يكون :attribute :min عام أو أحدث',
                'car_papers.*.type.required_with' => 'يجب تحديد نوع الورقة لكل ملف مرفوع في أوراق السيارة.',
                'car_papers.*.car_file.required_with' => 'يجب إرفاق الملف لكل نوع ورقة يتم اختياره في أوراق السيارة.',
            ],
            default => [],
        };
    }
    public function attributes(): array
    {
        return match ($this->route()->getActionMethod()) {
            'update_driver' => [
                'id'                   => 'معرف السائق',
                'first_name'           => 'الاسم الأول',
                'last_name'            => 'الكنية',
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

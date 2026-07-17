<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class ContractTermFormRequest extends FormRequest
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

            'create_contract_term' => [
                'order' => 'required|numeric|min:0',
                'term_text' => ['required', 'string', new NoHtml],
            ],
            'create_driver_contract' => [
                'company_name'   => 'required|string|max:255',
                'cr_number'      => 'nullable|string|max:100',
                'hq'             => 'required|string|max:255',
                'representative' => 'required|string|max:255',
                'name'             => 'required|string|max:255',
                'father_name'      => 'required|string|max:255',
                'mother_name'      => 'required|string|max:255',
                'birth_place_date' => 'required|string|max:255',
                'national_id'      => 'required|string|max:50',
                'amana'            => 'required|string|max:255',
                'qaid'             => 'required|string|max:255',
                'address'          => 'required|string|max:255',
                'grant_date'       => 'required|string|max:50',
            ],
            'update_contract_term_order' => [
                'order' => 'required|numeric|min:0',
            ],
            default => [],
        };
    }
    public function attributes(): array
    {
        return match ($this->route()->getActionMethod()) {
            'create_driver_contract' => [
                'company_name'   => 'اسم الشركة',
                'cr_number'      => 'رقم السجل التجاري',
                'hq'             => 'المقر',
                'representative' => 'اسم المفوض بالتوقيع',
                'name'             => 'الاسم الكامل للسائق',
                'father_name'      => 'اسم الأب',
                'mother_name'      => 'اسم الأم',
                'birth_place_date' => 'محل وتاريخ الولادة',
                'national_id'      => 'الرقم الوطني / رقم البطاقة',
                'amana'            => 'الأمانة',
                'qaid'             => 'القيد',
                'address'          => 'العنوان السكني',
                'grant_date'       => 'تاريخ منح البطاقة',
            ],
            default => [],
        };
    }
    public function messages(): array
    {
        return match ($this->route()->getActionMethod()) {

            'create_contract_term' => [
                'order.required' => 'ترتيب البند مطلوب',
                'order.numeric' => 'ترتيب البند يجب أن يكون رقماً',
                'order.min' => 'ترتيب البند يجب أن أكبر من الصفر',

                'term_text.required' => 'البند مطلوب',
                'term_text.string' => 'البند يجب أن يكون نصاً',
            ],
            'create_driver_contract' => [
                'required' => 'حقل :attribute مطلوب ولا يمكن تركه فارغاً.',
                'string'   => 'حقل :attribute يجب أن يكون نصاً.',
                'max'      => 'حقل :attribute تجاوز الحد الأقصى للأحرف المسموحة.',
            ],
            'update_contract_term_order' => [
                'order.required' => 'ترتيب البند مطلوب',
                'order.numeric' => 'ترتيب البند يجب أن يكون رقماً',
                'order.min' => 'ترتيب البند يجب أن أكبر من الصفر',
            ],
            default => [],
        };
    }
}

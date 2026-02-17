<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AuthFormRequest extends FormRequest
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
        'user_Register' => [
            'first_name' => ['required', 'string', 'max:255', new NoHtml],
            'last_name' => ['required', 'string', 'max:255', new NoHtml],
            'email' => 'required|email|unique:users',
            'phone_number' => ['required', 'string', 'max:50', new NoHtml],
            'location' => ['required', 'string', 'max:255', new NoHtml],
            'password' => [
                'required',
                'string',
                 Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ],
        'login' => [
            'email' => 'required|email',
            'password' => 'required|string',
        ],
        'send_email' => [
            'email' => 'required',
        ],
        'verification' => [
            'email' => 'required|email',
            'verification_code' => 'required|digits:6',
        ],
        'reset_password' => [
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'new_password' => [
                'required',
                'string',
                 Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ],

        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'user_Register' => [
                'first_name.required' => 'الاسم الأول مطلوب',
                'first_name.string' => 'الاسم الأول يجب أن يكون نصاً',
                'first_name.max' => 'الاسم الأول يجب ألا يتجاوز 255 حرفاً',

                'last_name.required' => 'اسم العائلة مطلوب',
                'last_name.string' => 'اسم العائلة يجب أن يكون نصاً',
                'last_name.max' => 'اسم العائلة يجب ألا يتجاوز 255 حرفاً',

                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
                'email.unique' => 'هذا البريد الإلكتروني مستخدم مسبقاً',

                'phone_number.required' => 'رقم الهاتف مطلوب',
                'phone_number.string' => 'رقم الهاتف يجب أن يكون نصاً',
                'phone_number.max' => 'رقم الهاتف يجب ألا يتجاوز 50 حرفاً',

                'location.required' => 'الموقع مطلوب',
                'location.string' => 'الموقع يجب أن يكون نصاً',
                'location.max' => 'الموقع يجب ألا يتجاوز 255 حرفاً',

                'password.required' => 'كلمة المرور مطلوبة',
                'password.*' => 'كلمة المرور يجب أن تحتوي على أحرف كبيرة وصغيرة وأرقام ورموز وألا تقل عن 8 أحرف',

            ],

            'login' => [
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
                'password.required' => 'كلمة المرور مطلوبة',
            ],

            'send_email' => [
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
                'email.exists' => 'هذا البريد الإلكتروني غير مسجل في النظام',
            ],

            'verification' => [
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
                'verification_code.required' => 'رمز التحقق مطلوب',
                'verification_code.digits' => 'رمز التحقق يجب أن يتكون من 6 أرقام',
            ],

            'reset_password' => [
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
                'reset_token.required' => 'رمز إعادة التعيين مطلوب',
                'new_password.required' => 'كلمة المرور الجديدة مطلوبة',
                'new_password.min' => 'كلمة المرور الجديدة يجب أن تحتوي على 8 أحرف على الأقل',
            ],

            default => [],
        };
    }

}

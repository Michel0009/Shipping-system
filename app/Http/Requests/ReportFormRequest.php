<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class ReportFormRequest extends FormRequest
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

        'report' => [
            'reported_id' => 'required|exists:users,id',
            'type' => ['required', 'string', 'max:255', new NoHtml],
            'description' => ['required', 'string', new NoHtml],
        ],
        'send_warning' => [
            'user_id' => 'required|exists:users,id',
            'warning_text' => ['required', 'string', new NoHtml],
        ],
        'send_notification_for_all' => [
            'notification_text' => ['required', 'string', new NoHtml],
        ],

        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'report' => [
                'type.required' => 'نوع الإبلاغ مطلوب',
                'type.string' => 'نوع الإبلاغ يجب أن يكون نصاً',
                'type.max' => 'نوع الإبلاغ يجب ألا يتجاوز 255 حرفاً',

                'description.required' => 'الوصف مطلوب',
                'description.string' => 'الوصف يجب أن يكون نصاً',

                'reported_id.required' => 'رقم المستخدم المبلّغ عنه مطلوب',
                'reported_id.exists' => 'رقم المستخدم المبلّغ عنه غير موجود',
            ],
            'send_warning' => [
                'warning_text.required' => 'نص التنبه مطلوب',
                'warning_text.string' => 'نص التنبيه يجب أن يكون نصاً',

                'user_id.required' => 'رقم المستخدم مطلوب',
                'user_id.exists' => 'رقم المستخدم غير موجود',
            ],
            'send_notification_for_all' => [
                'notification_text.required' => 'نص الإشعار مطلوب',
                'notification_text.string' => 'نص الإشعار يجب أن يكون نصاً',
            ],

            default => [],
        };
    }

}

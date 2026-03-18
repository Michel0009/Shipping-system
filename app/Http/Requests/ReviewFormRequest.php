<?php

namespace App\Http\Requests;

use App\Rules\NoHtml;
use Illuminate\Foundation\Http\FormRequest;

class ReviewFormRequest extends FormRequest
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
        'create_review' => [
            'driver_id' => 'required|exists:drivers,id',
            'rate' => 'required|numeric|min:1|max:5',
            'review' => ['required', 'string', new NoHtml],
        ],
    
        default => [],
      };
    }

    public function messages(): array
    {
        return match($this->route()->getActionMethod()) {

            'create_review' => [
                'rate.required' => 'التقييم مطلوب',
                'rate.numeric' => 'التقييم يجب أن يكون رقماً',
                'rate.max' => 'التقييم يجب ألا يتجاوز ال 5',
                'rate.min' => 'التقييم يجب ألا يقل عن ال 1',

                'review.required' => 'الوصف مطلوب',
                'review.string' => 'الوصف يجب أن يكون نصاً',

                'driver_id.required' => 'رقم السائق مطلوب',
                'driver_id.exists' => 'رقم السائق غير موجود',
            ],

            default => [],
        };
    }

}

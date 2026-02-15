<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoHtml implements Rule
{

    public function passes($attribute, $value)
    {

        if (strip_tags($value) !== $value) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'لا يُسمح بإدخال أكواد HTML أو JavaScript في هذا الحقل';
    }
}

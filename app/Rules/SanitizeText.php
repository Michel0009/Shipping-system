<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SanitizeText implements Rule
{

    public function passes($attribute, $value)
    {
        $sanitized = str_replace(['<', '>'], ['‹', '›'], $value);

        request()->merge([$attribute => $sanitized]);

        return true;
    }

    public function message()
    {
        return '';
    }
}

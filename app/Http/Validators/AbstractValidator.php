<?php

namespace App\Http\Validators;

interface AbstractValidator
{

    /**
     * @return array
     */
     public static function rules();

    /**
     * @return array
     */
    public static function messages();
}

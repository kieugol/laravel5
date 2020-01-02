<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckIsCsvFile implements Rule
{
    public $request;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $file = $this->request->file('file');
        if (!isset($file)) {
            return false;
        }
        $file->getRealPath();
        if ($file->getClientOriginalExtension() != 'csv') {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Wrong data format file.';
    }
}

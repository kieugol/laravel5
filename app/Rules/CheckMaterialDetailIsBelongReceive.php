<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckMaterialDetailIsBelongReceive implements Rule
{
    private $receive_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($recive_id)
    {
        $this->receive_id = $recive_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $count = DB::table("inventory_receive_detail")
            ->where('material_detail_id', $value)
            ->where('receive_id', $this->receive_id)
            ->count();
        if ($count) return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be have in receive.';
    }
}

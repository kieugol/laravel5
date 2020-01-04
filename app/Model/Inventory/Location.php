<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 5:38 PM
 */

namespace App\Model\Inventory;

use App\Model\BaseModel;

class Location extends BaseModel
{
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    protected $table = "inventory_location";

    protected $fillable = [
        "id",
        "name",
        "color_class",
        "is_active",
        "is_display",
        "created_by",
        "updated_by"
    ];
}

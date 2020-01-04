<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:21 PM
 */

namespace App\Repository\Inventory;

use App\Model\Inventory\WastedMaterial;
use App\Repository\BaseRepository;

class WastedMaterialRepository extends BaseRepository
{
    public function __construct(WastedMaterial $model)
    {
        parent::__construct($model);
    }
}

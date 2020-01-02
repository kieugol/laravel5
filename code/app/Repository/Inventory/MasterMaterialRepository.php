<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:52 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterMaterial;
use App\Repository\BaseRepository;

class MasterMaterialRepository extends BaseRepository
{
    public function __construct(MasterMaterial $model)
    {
        parent::__construct($model);
    }
}

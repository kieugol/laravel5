<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/25/2019
 * Time: 2:25 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\{MasterRecipeSku};
use App\Repository\BaseRepository;

class MasterRecipeSkuRepository extends BaseRepository
{

    public function __construct(MasterRecipeSku $model)
    {
        parent::__construct($model);
    }

}

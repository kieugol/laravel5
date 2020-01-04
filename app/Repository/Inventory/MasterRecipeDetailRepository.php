<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/25/2019
 * Time: 2:25 PM
 */

namespace App\Repository\Inventory;

use App\Model\Inventory\{MasterRecipeDetail};
use App\Repository\BaseRepository;

class MasterRecipeDetailRepository extends BaseRepository
{
    public function __construct(MasterRecipeDetail $model)
    {
        parent::__construct($model);
    }

}

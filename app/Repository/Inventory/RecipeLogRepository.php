<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:21 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\RecipeLog;
use App\Repository\BaseRepository;

class RecipeLogRepository extends BaseRepository
{
    public function __construct(RecipeLog $model)
    {
        parent::__construct($model);
    }
}

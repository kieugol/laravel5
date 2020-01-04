<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:07 PM
 */

namespace App\Repository;


use App\Model\MenuCategory;

class MenuCategoryRepository extends BaseRepository
{
    public function __construct(MenuCategory $model)
    {
        parent::__construct($model);
    }

}
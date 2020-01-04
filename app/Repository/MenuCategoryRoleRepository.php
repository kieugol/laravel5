<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:08 PM
 */

namespace App\Repository;


use App\Model\MenuCategoryRole;

class MenuCategoryRoleRepository extends BaseRepository
{
    public function __construct(MenuCategoryRole $model)
    {
        parent::__construct($model);
    }

}
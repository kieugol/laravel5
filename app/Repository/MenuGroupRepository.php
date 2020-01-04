<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:10 PM
 */

namespace App\Repository;


use App\Model\MenuGroup;

class MenuGroupRepository extends BaseRepository
{
    public function __construct(MenuGroup $model)
    {
        parent::__construct($model);
    }

}
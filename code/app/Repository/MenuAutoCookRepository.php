<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:06 PM
 */

namespace App\Repository;


use App\Model\MenuAutoCook;

class MenuAutoCookRepository extends BaseRepository
{
    public function __construct(MenuAutoCook $model)
    {
        parent::__construct($model);
    }

}
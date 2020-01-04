<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:55 PM
 */

namespace App\Repository;


use App\Model\ComboMenu;

class ComboMenuRepository extends BaseRepository
{
    public function __construct(ComboMenu $model)
    {
        parent::__construct($model);
    }

}
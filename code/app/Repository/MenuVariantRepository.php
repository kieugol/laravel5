<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:15 PM
 */

namespace App\Repository;


use App\Model\MenuVariant;

class MenuVariantRepository extends BaseRepository
{
    public function __construct(MenuVariant $model)
    {
        parent::__construct($model);
    }

}
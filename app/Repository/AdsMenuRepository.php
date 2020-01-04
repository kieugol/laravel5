<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/10/2019
 * Time: 10:35 AM
 */

namespace App\Repository;


use App\Model\AdsMenu;

class AdsMenuRepository extends BaseRepository
{
    public function __construct(AdsMenu $model)
    {
        parent::__construct($model);
    }

}
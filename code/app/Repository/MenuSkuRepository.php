<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:12 PM
 */

namespace App\Repository;


use App\Model\MenuSku;

class MenuSkuRepository extends BaseRepository
{
    public function __construct(MenuSku $model)
    {
        parent::__construct($model);
    }

}
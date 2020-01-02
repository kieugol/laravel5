<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:40 PM
 */

namespace App\Repository;


use App\Model\Sku;

class SkuRepository extends BaseRepository
{
    public function __construct(Sku $model)
    {
        parent::__construct($model);
    }

}
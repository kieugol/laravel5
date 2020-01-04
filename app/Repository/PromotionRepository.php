<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:38 PM
 */

namespace App\Repository;


use App\Model\Promotion;

class PromotionRepository extends BaseRepository
{
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

}
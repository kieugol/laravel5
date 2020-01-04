<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:58 PM
 */

namespace App\Repository;


use App\Model\ComboVariant;

class ComboVariantRepository extends BaseRepository
{
    public function __construct(ComboVariant $model)
    {
        parent::__construct($model);
    }

}
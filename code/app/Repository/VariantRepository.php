<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:49 PM
 */

namespace App\Repository;


use App\Model\Variant;

class VariantRepository extends BaseRepository
{
    public function __construct(Variant $model)
    {
        parent::__construct($model);
    }

}
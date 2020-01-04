<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:53 PM
 */

namespace App\Repository;


use App\Model\ComboGroup;

class ComboGroupRepository extends BaseRepository
{
    public function __construct(ComboGroup $model)
    {
        parent::__construct($model);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:50 PM
 */

namespace App\Repository;


use App\Model\Combo;

class ComboRepository extends BaseRepository
{
    public function __construct(Combo $model)
    {
        parent::__construct($model);
    }

}
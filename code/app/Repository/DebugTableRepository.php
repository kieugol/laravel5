<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 5:59 PM
 */

namespace App\Repository;


use App\Model\DebugTable;

class DebugTableRepository extends BaseRepository
{
    public function __construct(DebugTable $model)
    {
        parent::__construct($model);
    }

}
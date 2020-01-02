<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:17 PM
 */

namespace App\Repository;


use App\Model\CCOrderUpdateStatus;

class CCOrderUpdateStatusRepository extends BaseRepository
{
    public function __construct(CCOrderUpdateStatus $model)
    {
        parent::__construct($model);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:46 PM
 */

namespace App\Repository;


use App\Model\CCOrderResyncStatus;

class CCOrderResyncStatusRepository extends BaseRepository
{
    public function __construct(CCOrderResyncStatus $model)
    {
        parent::__construct($model);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:14 PM
 */

namespace App\Repository;


use App\Model\UserCheckin;

class UserCheckinRepository extends BaseRepository
{
    public function __construct(UserCheckin $model)
    {
        parent::__construct($model);
    }

}
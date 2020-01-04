<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:16 PM
 */

namespace App\Repository;


use App\Model\UserToken;

class UserTokenRepository extends BaseRepository
{
    public function __construct(UserToken $model)
    {
        parent::__construct($model);
    }

}
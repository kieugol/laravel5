<?php

/**
 * Created by PhpStorm.
 * User: ThieuPham
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository;

use App\Model\UserDriver;

class UserDriverRepository extends BaseRepository {

    public function __construct(UserDriver $model) {
        parent::__construct($model);
    }

}

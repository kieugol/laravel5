<?php

/**
 * Created by PhpStorm.
 * User: ThieuPham
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository;

use App\Model\User;
//use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository {

    public function __construct(User $model) {
        parent::__construct($model);
    }

}

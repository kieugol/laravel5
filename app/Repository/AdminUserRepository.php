<?php

/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository;

use App\Model\UserAdmin;

//use Illuminate\Support\Facades\DB;

class AdminUserRepository extends BaseRepository {

    public function __construct(UserAdmin $model) {
        parent::__construct($model);
    }

    public function getRoleCurrentUser()
    {
        $user = auth("admin")->user();
        return $this->model->select([
            'admin_roles.name'
        ])
            ->join('admin_role_users', 'admin_role_users.user_id', 'admin_users.id')
            ->join('admin_roles', 'admin_roles.id', 'admin_role_users.role_id')
            ->where('admin_users.id', $user->id)
            ->first()->name;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: DuongTram
 * Date: 10/18/2018
 * Time: 4:29 PM
 */

namespace App\Repository;

use App\Model\Role;

class RoleRepository extends BaseRepository {

    public function __construct(Role $model) {
        parent::__construct($model);
    }

    public function getAllRoles()
    {
        $roles = $this->model->whereIn('id', [
            ROLE_MANAGER,
            ROLE_DRIVER,
            ROLE_COOKER,
            ROLE_CASHIER,
        ])->pluck('name', 'id');
        
        return $roles;
    }

}

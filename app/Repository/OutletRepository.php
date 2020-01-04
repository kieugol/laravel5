<?php

namespace App\Repository;

use App\Model\Outlet;

class OutletRepository extends BaseRepository {

    public function __construct(Outlet $model) {
        parent::__construct($model);
    }

}

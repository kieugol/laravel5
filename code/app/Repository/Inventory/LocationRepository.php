<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 5:40 PM
 */

namespace App\Repository\Inventory;

use App\Model\Inventory\Location;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class LocationRepository extends BaseRepository
{
    public function __construct(Location $model)
    {
        parent::__construct($model);
    }

    public function getByArrayId(array $ids, $order = 'ASC')
    {
        return $this->model->whereIn(Location::getCol('id'), $ids)->orderBy(Location::getCol('id'), $order)->get();
    }
}

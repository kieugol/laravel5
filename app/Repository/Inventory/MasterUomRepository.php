<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 5:21 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterUom;
use App\Repository\BaseRepository;

class MasterUomRepository extends BaseRepository
{
    public function __construct(MasterUom $model)
    {
        parent::__construct($model);
    }

    /**
     * get uom by id
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->model->where('id', $id)->first();
    }

}
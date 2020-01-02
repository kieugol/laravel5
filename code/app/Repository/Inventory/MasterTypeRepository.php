<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 4:52 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterType;
use App\Repository\BaseRepository;

class MasterTypeRepository extends BaseRepository
{
    public function __construct(MasterType $model)
    {
        parent::__construct($model);
    }

    public function getList()
    {
        return $this->model->select('*')->get();
    }
}

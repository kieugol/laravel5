<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 5:21 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterUomDetail;
use App\Repository\BaseRepository;

class MasterUomDetailRepository extends BaseRepository
{
    public function __construct(MasterUomDetail $model)
    {
        parent::__construct($model);
    }

}
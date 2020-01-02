<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 4:52 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterGenstore;
use App\Repository\BaseRepository;

class MasterGenstoreRepository extends BaseRepository
{
    public function __construct(MasterGenstore $model)
    {
        parent::__construct($model);
    }

}

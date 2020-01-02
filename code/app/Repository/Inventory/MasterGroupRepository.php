<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 4:52 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterGroup;
use App\Repository\BaseRepository;

class MasterGroupRepository extends BaseRepository
{
    public function __construct(MasterGroup $model)
    {
        parent::__construct($model);
    }

}

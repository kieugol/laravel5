<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 4:52 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\SyncMaster;
use App\Repository\BaseRepository;

class SyncMasterRepository extends BaseRepository
{
    public function __construct(SyncMaster $model)
    {
        parent::__construct($model);
    }

}

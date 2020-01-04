<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/21/2019
 * Time: 4:52 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterAccount;
use App\Repository\BaseRepository;

class MasterAccountRepository extends BaseRepository
{
    public function __construct(MasterAccount $model)
    {
        parent::__construct($model);
    }

}

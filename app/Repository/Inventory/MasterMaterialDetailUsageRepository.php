<?php

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterMaterialDetailUsage;
use App\Repository\BaseRepository;

class MasterMaterialDetailUsageRepository extends BaseRepository
{
    public function __construct(MasterMaterialDetailUsage $model)
    {
        parent::__construct($model);
    }

}

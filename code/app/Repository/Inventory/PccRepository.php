<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 4:40 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\MasterPCC;
use App\Repository\BaseRepository;

class PccRepository extends BaseRepository
{
    public function __construct(MasterPCC $model)
    {
        parent::__construct($model);
    }

    /**
     * @return mixed
     */
    public function getPcc()
    {
        $year    = date("Y");
        $month   = date("m");
        $date    = date("d");
        return $this->model
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('from_date', '<=', $date)
                    ->where('to_date', '>=', $date)
                    ->first();
    }
}

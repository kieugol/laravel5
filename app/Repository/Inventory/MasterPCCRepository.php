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

class MasterPCCRepository extends BaseRepository
{
    public function __construct(MasterPCC $model)
    {
        parent::__construct($model);
    }
    
    public function getAllByPeriod($month, $year = '')
    {
        $year = empty($year) ? date('Y') : $year;
        
       return $this->model
           ->select("*")
           ->where(MasterPCC::getCol('month'), $month)
           ->where(MasterPCC::getCol('year'), $year)
           ->where(MasterPCC::getCol('period'), '<>', PERIOD_PCC_4)
           ->get();
    }
    
    public function getPeriodByPcc($month, $year, $period)
    {
        $pcc =  $this->model
            ->select("*")
            ->where(MasterPCC::getCol('year'), $year)
            ->where(MasterPCC::getCol('month'), $month)
            ->where(MasterPCC::getCol('period'), $period)
            ->first();
        
        return $pcc ? ['fromDate' => "$pcc->year-$month-$pcc->from_date", 'toDate' => "$pcc->year-$month-$pcc->to_date"] : [];
    }
    
    /**
     * @return mixed
     */
    public function getByCurrentDate()
    {
        $year    = date("Y");
        $month   = date("m");
        $date    = date("d");
        return $this->model
            ->where('year', $year)
            ->where('month', $month)
            ->where('from_date', '<=', $date)
            ->where('to_date', '>=', $date)
            ->get()
            ->first();
    }
}

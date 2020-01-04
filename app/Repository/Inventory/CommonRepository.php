<?php
/**
 * Created by PhpStorm.
 * User: An Huynh
 * Date: 13/9/2019
 * Time: 2:45 PM
 */

namespace App\Repository\Inventory;

use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class CommonRepository extends BaseRepository
{
    public function __construct()
    {

    }

    /**
     * get potential price for potential report, DRS report page
     * @param $param
     * @return mixed
     */
    public function getPotentialPrice($param)
    {
        $return = DB::select("SELECT f_sum_total_material_price('".$param['fromDate']."','".$param['toDate']."') AS potential_price;");

        return $return;
    }
}

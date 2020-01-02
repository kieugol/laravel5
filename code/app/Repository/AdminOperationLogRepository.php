<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 5:51 PM
 */

namespace App\Repository;


use App\Model\AdminOperationLog;
use Illuminate\Support\Facades\DB;

class AdminOperationLogRepository extends BaseRepository
{

    public function __construct(AdminOperationLog $model)
    {
        parent::__construct($model);
    }

}
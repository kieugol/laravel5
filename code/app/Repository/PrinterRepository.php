<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:36 PM
 */

namespace App\Repository;


use App\Model\Printer;

class PrinterRepository extends BaseRepository
{
    public function __construct(Printer $model)
    {
        parent::__construct($model);
    }

}
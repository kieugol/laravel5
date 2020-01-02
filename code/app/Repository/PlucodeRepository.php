<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:20 PM
 */

namespace App\Repository;


use App\Model\Plucode;

class PlucodeRepository extends BaseRepository
{
    public function __construct(Plucode $model)
    {
        parent::__construct($model);
    }

}
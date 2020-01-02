<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:04 PM
 */

namespace App\Repository;


use App\Model\LogClient;

class LogClientRepository extends BaseRepository
{
    public function __construct(LogClient $model)
    {
        parent::__construct($model);
    }

}
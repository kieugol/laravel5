<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:39 PM
 */

namespace App\Repository;


use App\Model\CCAddress;

class CCAddressRepository extends BaseRepository
{
    public function __construct(CCAddress $model)
    {
        parent::__construct($model);
    }

}
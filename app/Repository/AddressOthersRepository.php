<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:22 PM
 */

namespace App\Repository;


use App\Model\AddressOthers;

class AddressOthersRepository extends BaseRepository
{
    public function __construct(AddressOthers $model)
    {
        parent::__construct($model);
    }

}
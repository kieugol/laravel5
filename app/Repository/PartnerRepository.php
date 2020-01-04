<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 3:17 PM
 */

namespace App\Repository;


use App\Model\Partner;

class PartnerRepository extends BaseRepository
{
    public function __construct(Partner $model)
    {
        parent::__construct($model);
    }

}
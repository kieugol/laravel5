<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:57 PM
 */

namespace App\Repository;


use App\Model\ComboMenuOption;

class ComboMenuOptionRepository extends BaseRepository
{
    public function __construct(ComboMenuOption $model)
    {
        parent::__construct($model);
    }

}
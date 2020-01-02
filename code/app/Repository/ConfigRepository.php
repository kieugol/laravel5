<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/9/2019
 * Time: 2:22 PM
 */

namespace App\Repository;


use App\Model\Config;

class ConfigRepository extends BaseRepository
{
    public function __construct(Config $model)
    {
        parent::__construct($model);
    }

    public function getStoreCode()
    {
        $config = $this->model->select('value')->where('key', 'outlet_code')->first();
        return $config->value;
    }
}

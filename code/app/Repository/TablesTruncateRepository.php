<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 1/8/2019
 * Time: 11:03 AM
 */

namespace App\Repository;


use App\Model\TablesTruncate;

class TablesTruncateRepository extends BaseRepository
{
    public function __construct(TablesTruncate $model) {
        parent::__construct($model);
    }

    public function getListTablesTruncate()
    {
        return $this->getModel()
            ->get();
    }

    public function checkIsActiveTable($table_name)
    {
        return $this->getModel()->where('table_name', $table_name)->value('is_active');
    }
}
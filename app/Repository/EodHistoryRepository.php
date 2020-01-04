<?php

/**
 * Created by PhpStorm.
 * User: ThieuPham
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository;

use App\Model\EodHistory;
use Illuminate\Support\Facades\DB;

class EodHistoryRepository extends BaseRepository {

//    protected $model;
//
//    /**
//     * ArticleRepository constructor.
//     */
    public function __construct(EodHistory $model) {
        parent::__construct($model);
    }

    public function getLastItem() {
        return $this->getModel()->query()->orderBy("id", "desc")->first();
    }

//    public function pagination() {
//        return $this->model->filterPaginateOrder();
//    }

}

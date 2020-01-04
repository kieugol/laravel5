<?php

namespace App\Repository;

use App\Model\{Order, OrderDetailLog, MenuSku, User};

class OrderDetailLogRepository extends BaseRepository {

    public function __construct(OrderDetailLog $model) {
        parent::__construct($model);
    }

    public function getActivitylogOrder($param = [])
    {

        $query = $this->model
            ->select([
                Order::getColumnName("number"),
                OrderDetailLog::getColumnName("*"),
                User::getColumnName('name') . " AS user_name",
                MenuSku::getColumnName('sku')
            ])
            ->join(Order::getTableName(), Order::getColumnName("id"), OrderDetailLog::getColumnName("order_id"))
            ->leftJoin(MenuSku::getTableName(), MenuSku::getColumnName("plucode"), OrderDetailLog::getColumnName("plucode"))
            ->leftJoin(User::getTableName(), User::getColumnName("id"), OrderDetailLog::getColumnName("created_by"));

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query->whereRaw("DATE(". OrderDetailLog::getColumnName("created_date") . ") >= '{$param['fromDate']}'");
            }

            if (!empty($param['toDate'])) {
                $query->whereRaw("DATE(". OrderDetailLog::getColumnName("created_date") . ") <= '{$param['toDate']}'");
            }

            if (!empty($param['action_edit'])) {
                $query->where(OrderDetailLog::getColumnName("action"), "=", $param['action_edit']);
            }
        }

        $query->orderBy(OrderDetailLog::getColumnName("created_date"), "DESC");
        $query->orderBy(OrderDetailLog::getColumnName("order_id"), "DESC");
        $query->orderBy(OrderDetailLog::getColumnName("action"), "DESC");

        $result = $query->get()->toArray();

        return $result;
    }
}

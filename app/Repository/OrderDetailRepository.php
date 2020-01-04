<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 6:03 PM
 */

namespace App\Repository;

use App\Model\{Addon, Menu, MenuCategory, Variant, MenuVariant, Order, OrderDetail};

class OrderDetailRepository extends BaseRepository
{
    public function __construct(OrderDetail $model)
    {
        parent::__construct($model);
    }
    
    public function getReportSaleMixMenu($param = null)
    {
        $query = $this->model
            ->select([
                "category_tmp.name AS category_name_tmp",
                MenuCategory::getColumnName('name AS category_name'),
                MenuCategory::getColumnName('id AS category_id'),
                OrderDetail::getColumnName('menu_name'),
                OrderDetail::getColumnName('addon_name'),
                OrderDetail::getColumnName('variant_name'),
                OrderDetail::getColumnName('variant_id'),
                OrderDetail::getColumnName('addon_id'),
                OrderDetail::getColumnName('menu_name AS menu_name_ordered'),
                OrderDetail::getColumnName('menu_id'),
                OrderDetail::getColumnName('plucode'),
                OrderDetail::getColumnName('id AS order_detail_id'),
                OrderDetail::getColumnName('quantity'),
                Order::getColumnName('order_type_id'),
                Order::getColumnName('date'),
                Order::getColumnName('created_date'),
            ])
            ->join(Order::getTableName(), Order::getColumnName('id'), OrderDetail::getColumnName('order_id'))
            ->leftJoin(MenuCategory::getTableName(), MenuCategory::getColumnName('id'), OrderDetail::getColumnName('category_id'))
            ->leftJoin(MenuCategory::getTableName() . " AS category_tmp", 'category_tmp.id', OrderDetail::getColumnName('category_id'))
            ->where(Order::getColumnName('order_status_id'), ORDER_STATUS_FINISHED)
            ->where(Order::getColumnName('is_oc'), STATUS_INACTIVE)
            ->where(Order::getColumnName('is_meals_outlet'), STATUS_INACTIVE)
            ->where(OrderDetail::getColumnName('is_delete'), STATUS_INACTIVE)
            ->where(OrderDetail::getColumnName('is_combo'), STATUS_INACTIVE)
            ->orderBy(OrderDetail::getColumnName('category_id'), "ASC")
            ->orderBy(OrderDetail::getColumnName('variant_id'), "ASC")
            ->orderBy(OrderDetail::getColumnName('addon_id'), "ASC")
            ->orderBy(OrderDetail::getColumnName('menu_id'), "ASC");
        
        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query->where(Order::getColumnName("date"), ">=", $param['fromDate']);
            }
            
            if (isset($param['toDate'])) {
                $query->where(Order::getColumnName("date"), "<=", $param['toDate']);
            }
            
            if (isset($param['fromTime'])) {
                $query->where(Order::getColumnName("created_date"), ">", $param['fromTime']);
            }
            
            if (isset($param['toTime'])) {
                $query->where(Order::getColumnName("created_date"), "<", $param['toTime']);
                
            }
        }
        
        return $query->get()->toArray();
    }
}

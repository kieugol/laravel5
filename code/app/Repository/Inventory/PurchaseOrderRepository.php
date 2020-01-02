<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\PurchaseOrder;
use App\Model\Inventory\ReceiveOrder;
use App\Repository\BaseRepository;

class PurchaseOrderRepository extends BaseRepository
{
    public function __construct(PurchaseOrder $model)
    {
        parent::__construct($model);
    }

    public function generateCode()
    {
        $num = '0000001';
        $obj = $this->model->orderBy('id','DESC')->first();

        if ($obj) {
            $objCode = $obj->code;
            $num = $objCode + 1;
            $num = str_pad($num, 7, '0', STR_PAD_LEFT);
        }

        $codeNext = $num;

        return $codeNext;
    }
    
    public function getListPurchase($filters = [], $search = [], $sort = [], $period = [],$limit = 10, $offset = 0, $is_received = null)
    {
        $query = $this->model
            ->select([
                PurchaseOrder::getCol('id'),
                PurchaseOrder::getCol('store_code'),
                PurchaseOrder::getCol('code'),
                PurchaseOrder::getCol('supplier_id'),
                PurchaseOrder::getCol('pcc_id'),
                PurchaseOrder::getCol('delivery_date'),
                PurchaseOrder::getCol('quantity'),
                PurchaseOrder::getCol('total'),
                PurchaseOrder::getCol('description'),
                PurchaseOrder::getCol('status_id'),
                PurchaseOrder::getCol('confirmed_date'),
                PurchaseOrder::getCol('path'),
                PurchaseOrder::getCol('file_name'),
                PurchaseOrder::getCol('is_active'),
                PurchaseOrder::getCol('created_date'),
                PurchaseOrder::getCol('updated_date'),
                PurchaseOrder::getCol('created_by'),
                PurchaseOrder::getCol('updated_by'),
                ReceiveOrder::getCol('invoice_number AS invoice_number'),
                ReceiveOrder::getCol('id AS receive_id'),
                ReceiveOrder::getCol('status_id AS receive_status_id')
            ])
            ->leftJoin(ReceiveOrder::getTbl(), PurchaseOrder::getCol('id'), ReceiveOrder::getCol('purchase_id'));
        
        // Get list purchase not receive yet
        if (!$is_received) {
           $query = $query->whereRaw('`inventory_receive`.`id` is null or `inventory_receive`.`status_id` !=' . TRANSACTION_ORDER_STATUS_APPROVED);
        }
        
        if (sizeof($filters) > 0) {
            foreach ($filters as $field => $key) {
                $query = $query->where($field, $key);
            }
        }
        
        if (sizeof($search) > 0) {
            foreach ($search as $field => $value) {
                if (!empty($value)) {
                    $query = $query->where($field, 'like', "%" . $value . "%");
                }
            }
        }
        
        if (!empty($period['fromDate'])) {
            $query = $query->whereRaw("date(created_date) >= '" . $period['fromDate'] . "'");
        }
        
        if (!empty($period['toDate'])) {
            $query = $query->whereRaw("date(created_date) <= '" . $period['toDate'] . "'");
        }
        
        $total = $query->count();
        
        if (sizeof($sort) > 0) {
            foreach ($sort as $field => $type) {
                $query = $query->orderBy($field, $type);
            }
        } else {
            $query = $query->orderBy("id", "DESC");
        }
        $query = $query->offset($offset * $limit);
        $query = $query->limit($limit);
        
        return ['items' => $query->get(), 'total' => $total, 'limit' => $limit, 'offset' => $offset];
    }

}

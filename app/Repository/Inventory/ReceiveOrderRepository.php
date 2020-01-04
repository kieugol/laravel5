<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\ReceiveOrder;
use App\Repository\BaseRepository;

class ReceiveOrderRepository extends BaseRepository
{
    public function __construct(ReceiveOrder $model)
    {
        parent::__construct($model);
    }

    /**
     * get returnable order for return order page
     * @return mixed
     */
    public function getReturnableOrder()
    {
        // invoice which able to Returnable is last 10 days
        $returnable_date = date('Y-m-d', strtotime('-10 days'));

        $result = $this->model
            ->where('is_returnable', STATUS_ACTIVE)
            ->where('status_id', TRANSACTION_ORDER_STATUS_APPROVED)
            ->WhereRaw("date(updated_date) >= '".$returnable_date."'")
            ->orderBy($this->model->getKeyName(), 'DESC')
            ->get();

        return $result;
    }

    public function getByPeriod($from_date, $to_date) {
        return $this->model
            ->whereRaw("updated_date >= '".$from_date."'")
            ->whereRaw("updated_date <= '".$to_date."'")
            ->get();
    }


    public function getLatest() {
        return $this->model
            ->where('status_id', TRANSACTION_ORDER_STATUS_APPROVED)
            ->orderBy('id', 'desc')
            ->first();
    }
}

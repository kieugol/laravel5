<?php

namespace App\Repository\Inventory;

use App\Helpers\FileHelper;
use App\Helpers\PosHelper;
use App\Model\Inventory\ViewDailyReceivingSummary;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class ViewDailyReceivingSummaryRepository extends BaseRepository
{
    public function __construct(ViewDailyReceivingSummary $model)
    {
        parent::__construct($model);
    }

    public function getListReport($param)
    {
        $query_builder = $this->model->select([
            'account_code',
            DB::raw('SUM(total) AS total'),
            'date_time'
        ]);

        if (!empty($param['fromDate'])) {
            $query_builder->whereRaw("date_time >= '". $param['fromDate']."'");
        }
        if (!empty($param['toDate'])) {
            $query_builder->whereRaw("date_time <= '". $param['toDate']."'");
        }

        $query_builder->groupBy(ViewDailyReceivingSummary::getCol('account_code'));
        $items = $query_builder->get();
        return $items;
    }

    public function zipFileTransaction($storeCode, $stock_opnames, $receives, $returns, $transfers, $from_date, $destination_contain = INVENTORY_DIR_DAILY_SHEET_CSV)
    {
        $sub_path         = date("Y/m/d", strtotime($from_date));
        $flag_folder      = FileHelper::create_sub_folder($destination_contain, $sub_path);
        // Stock opname
        if (!$stock_opnames->isEmpty()) {
            $files_stock_opname = [];
            foreach ($stock_opnames as $item) {
                $files_stock_opname[] = $item->path . DIRECTORY_SEPARATOR . $item->file_name;
            }

            join_files(
                $files_stock_opname,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_STOCK_OPNAME_TEMP
            );

            remove_blank_row_csv(
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_STOCK_OPNAME_TEMP,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_STOCK_OPNAME
            );
            unlink($flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_STOCK_OPNAME_TEMP);

        }

        // Receive
        if (!$receives->isEmpty()) {
            $files_receive = [];
            foreach ($receives as $item) {
                if ($item->status_id === TRANSACTION_ORDER_STATUS_APPROVED) {
                    $files_receive[] = $item->path . DIRECTORY_SEPARATOR . $item->file_name;
                }
            }

            join_files(
                $files_receive,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RECEIVE_TEMP
            );

            remove_blank_row_csv(
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RECEIVE_TEMP,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RECEIVE
            );
            unlink($flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RECEIVE_TEMP);
        }

        // Return
        if (!$returns->isEmpty()) {
            $files_return = [];
            foreach ($returns as $item) {
                $files_return[] = $item->path . DIRECTORY_SEPARATOR . $item->file_name;
            }

            join_files(
                $files_return,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RETURN_TEMP
            );

            remove_blank_row_csv(
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RETURN_TEMP,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RETURN
            );
            unlink($flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_RETURN_TEMP);
        }

        // Transfer
        if (!$transfers->isEmpty()) {
            $files_transfer_cosyst = [];
            foreach ($transfers as $item) {
                $files_transfer_cosyst[] = $item->path_cosyst . DIRECTORY_SEPARATOR . $item->file_name_cosyst;
            }

            join_files(
                $files_transfer_cosyst,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_TRANSFER_COSYST_TEMP
            );
            remove_blank_row_csv(
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_TRANSFER_COSYST_TEMP,
                $flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_TRANSFER_COSYST
            );
            unlink($flag_folder . DIRECTORY_SEPARATOR . $storeCode . CSV_NAME_TRANSFER_COSYST_TEMP);
        }

        $files         = glob($flag_folder . '/*.csv');
        $file_name_zip = $storeCode . '-' . date("d", strtotime($from_date)) . '.rar';
        if ($files) {
            $pwd      = PASSWORD_ZIP_DRS;
            $listFile = "";
            foreach ($files as $pathFile) {
                $listFile .= "  " . basename($pathFile);
            }

            $cmdCreateFile = "cd $flag_folder  && zip --password $pwd $file_name_zip $listFile";
            exec($cmdCreateFile, $output, $return);

            if (!$return) {
                $saleFileName = $storeCode . CSV_NAME_SALE_DATA;
                // Remove file after rar
                foreach ($files as $pathFile) {
                    if (strpos($pathFile, $saleFileName) === false) {
                        unlink($pathFile);
                    }
                }
                return [
                    'path'      => $flag_folder,
                    'file_name' => $file_name_zip
                ];
            }

            return false;
        }

        return false;
    }
}

<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('/test', 'TestController@index');
    $router->get('/home', 'HomeController@index');

    $router->get('/report/payment-non-cash', 'ReportController@payment_non_cash')
            ->name('report-payment-non-cash');
    $router->get('/report/summary-log', 'ReportController@summary_log')
            ->name('report-summary-log');
    $router->get('/report/summary-log-v2', 'ReportController@summary_log_v2')
            ->name('report-summary-log-v2');
    $router->get('/report/summary-log-v3', 'ReportController@summary_log_v3')
            ->name('report-summary-log-v3');
    $router->get('/report/summary-log-v4', 'ReportController@summary_log_v4')
            ->name('report-summary-log-v4');
    $router->get('/report/partner', 'ReportController@partner')
            ->name('report-partner');
    $router->get('/report/sales-mix-by-segment', 'ReportController@sales_mix_by_segment')
            ->name('report-sales-mix-by-segment');
    $router->get('/report/sales-mix-by-hour', 'ReportController@sales_mix_by_hour')
            ->name('report-sales-mix-by-hour');
    $router->get('/report/sales-mix-by-hour-v2', 'ReportController@sales_mix_by_hour_v2')
            ->name('report-sales-mix-by-hour-v2');
    $router->get('/report/sales-mix-by-segment-oc', 'ReportController@sales_mix_by_segment_oc')
            ->name('report-sales-mix-by-segment-oc');
    $router->get('/report/summary', 'ReportController@summary')
            ->name('report-summary');
    $router->get('/report/summary-oc', 'ReportController@summary_oc')
            ->name('report-summary-oc');
    $router->get('/report/summary-void', 'ReportController@summary_void')
            ->name('report-summary-void');
    $router->get('/report/speed-service/takeaway', 'ReportController@speed_service_takeaway')
            ->name('report-takeaway');
    $router->get('/report/speed-service/delivery', 'ReportController@speed_service_delivery')
        ->name('report-delivery');
    $router->get('/report/speed-service/swipe-done', 'ReportController@speed_service_swipedone')
        ->name('report-swipedone');
    $router->get('/report/summary-usage', 'ReportController@summary_usage')
        ->name('summary-usage');
    $router->get('/report/customer-ordering', 'ReportController@customer_ordering')
            ->name('report-customer-ordering');
    $router->get('/report/net-sales-and-bill-by-hours', 'ReportController@net_sales_and_bill_by_hours')
            ->name('report-net-sales-and-bill-by-hours');
    $router->get('/report/setoran-tunai-bank', 'ReportController@setoran_tunai_bank')
            ->name('report-setoran-tunai-bank');
    $router->get('/report/bill-by-poding', 'ReportController@bill_by_poding')
            ->name('report-bill-by-poding');
    $router->get('/report/donation-detail', 'ReportController@donation_detail')
            ->name('report-donation-detail');
    $router->get('/report/payment-detail', 'ReportController@payment_detail')
            ->name('report-payment-detail');
    $router->get('/report/payment-type-by-cashier', 'ReportController@payment_type_by_cashier')
            ->name('report-payment-type-by-cashier');
    $router->get('/report/history-by-bill', 'ReportController@history_by_bill')
            ->name('report-history-by-bill');
    $router->get('/report/activity-log-edit-order', 'ReportOrderController@getActivityLogOrder')
        ->name('report-activity-log-edit-order');
    $router->get('/report/report-pizza-size', 'ReportController@report_pizza_size')
        ->name('report-pizza-size');

    $router->post('/report/print-bill', 'ReportController@print_bill')
            ->name('report-print-bill');
    $router->get('/report/check_eod', 'ReportController@check_eod')
            ->name('check_eod');
    $router->get('/report/finish_eod', 'ReportController@finish_eod')
            ->name('finish_eod');
    $router->get('/report/push_ftp', 'ReportController@push_ftp')
            ->name('push_ftp');
    $router->get('/report/send_ftp_eod', 'ReportController@send_ftp_eod')
            ->name('send_ftp_eod');
    $router->get('/sync/menu', 'SyncController@menu')
            ->name('sync-menu');
    $router->get('/sync/promotion', 'SyncController@promotion')
            ->name('sync-promotion');
    $router->get('/download-eod-csv', 'EODController@downloadFile')
            ->name('download_eod_csv');
    $router->get('/resync-order-status', 'OrderResyncStatusController@resyncOrderStatus')
            ->name('resync_order_status');
    $router->get('/resync-menu', 'SyncMenuController@resyncMenu')
            ->name('resync_menu');
    $router->get('/inventory', 'InventoryController@index')
            ->name('admin.inventory');
    $router->get('/edit-order/{id}', 'OrderController@edit')->name('edit_order');
    $router->get('/edit-order-detail/{id}', 'OrderController@editOrderDetail')
            ->name('edit_order_detail');
    $router->get('/edit-order-payment/{id}', 'OrderController@editOrderPayment')
            ->name('edit_order_payment');
    $router->post('/update-order/{id}', 'OrderController@update_order')
            ->name('update_order');
    $router->post('/update-order-detail/{id}', 'OrderController@updateOrderDetail')
            ->name('update_order_detail');
    $router->post('/update-order-payment/{id}', 'OrderController@updateOrderPayment')
            ->name('update_order_payment');
    $router->get('/order-detail-view/{id}', 'OrderController@orderdetailView')
            ->name('order_detail_view');
    $router->get('/payment-detail-view/{id}', 'OrderController@paymentdetailView')
            ->name('payment_detail_view');
    $router->get('/delivery-detail-view/{id}', 'OrderController@deliverydetailView')
            ->name('delivery_detail_view');
    $router->get('/log-detail-view/{id}', 'OrderController@logdetailView')
            ->name('log_detail_view');
    $router->get('/log-print-view/{id}', 'OrderController@logPrintView')
            ->name('log_print_view');
    $router->get('/re-sync-order-delivery', 'SyncOrderDeliveryController@resyncOrderDelivery')
            ->name('re_sync_order_delivery');
    $router->post('/update-status-success/{id}', 'LogCurlController@update_status_success')
            ->name('update_status_success');
    $router->post('/truncate-table', 'CleanDataController@truncateTable')
            ->name('truncate_table');
    $router->post('/update-status-log-jobs/{id}', 'SyncOrderPosToJumpboxController@updateStatusLogJobs')
        ->name('update_status_log_jobs');
    $router->get('/import-cc-address', 'CCAddressController@importCCAddress')
        ->name('import_cc_address');
    $router->post('/save-address', 'CCAddressController@saveAddress');
    $router->get('/report/report-sale-mix-menu', 'SaleReportController@saleMixByMenu')
        ->name('report-sales-mix-by-menu');

    $router->resource('auth/roles', AdminRoleController::class);
    $router->resource('auth/users', AdminUserController::class);
    $router->resource('user', UserController::class);
    $router->resource('user-driver', UserDriverController::class);
    $router->resource('sku', SkuController::class);
    $router->resource('sku-quantity', SkuQuantityController::class);
    $router->resource('uom', UomController::class);
    $router->resource('recipe', RecipeController::class);
    $router->resource('recipe-configuration', RecipeConfigurationController::class);
    $router->resource('ads-monitor', AdsMonitorController::class);
    $router->resource('ads-menu', AdsMenuController::class);
    $router->resource('menu-auto-cook', MenuAutoCookController::class);
    $router->resource('printer', PrinterController::class);
    $router->resource('config', ConfigController::class);
    $router->resource('log-curl', LogCurlController::class);
    $router->resource('order-log-status', OrderLogStatusController::class);
    $router->resource('log-access-api', ApiAccessLogsController::class);
    $router->resource('shift-transaction', ShiftTransactionController::class);
    $router->resource('revert-order', OrderRevertController::class);
    $router->resource('eod-list', EODController::class);
    $router->resource('resync-order-status-online-list', OrderResyncStatusController::class);
    $router->resource('sync-menu', SyncMenuController::class);
    $router->resource('order', OrderController::class);
    $router->resource('payment-method', PaymentMethodController::class);
    $router->resource('cc-address', CCAddressController::class);
    $router->resource('sku-list', MenuSkuController::class);
    $router->resource('address-others', AddressOthersController::class);
    $router->resource('sync-order-into-online', SyncOrderDeliveryController::class);
    $router->resource('manage-order-taker', AppOrderTakerController::class);
    $router->resource('clean-data', CleanDataController::class);
    $router->resource('sync-pos-jumpbox', SyncOrderPosToJumpboxController::class);
    
    /*
      |-----------------------------------------------------------------------------------------------------------------
      | inventory
      |-----------------------------------------------------------------------------------------------------------------
     */
    // stock option name
    $router->resource('inventory/stock-opname', Inventory\StockOpnameController::class);
    $router->post('/inventory/stock-opname/save', 'Inventory\StockOpnameController@save');
    $router->post('/inventory/stock-opname/update', 'Inventory\StockOpnameController@update');
    $router->get('/inventory/stock-opname/report-pcc/{pcc_id}', 'Inventory\StockOpnameController@generatePCC')
        ->name('stock-opname-pcc-report');
    $router->get('/inventory/stock-opname/report-mcc/{pcc_id}', 'Inventory\StockOpnameController@generateMCC')
        ->name('stock-opname-mcc-report');
    $router->get('/inventory/stock-opname/report-stockopname/{id}', 'Inventory\StockOpnameController@generateStockOpname')
        ->name('stock-opname-report');
    $router->get('/inventory/stock-opname/update-status/{id}', 'Inventory\StockOpnameController@updateStatus')
        ->name('stock-opname-update-status');
    // material group
    $router->resource('inventory/material-group', MaterialGroupController::class);
    // material type
    $router->resource('inventory/material-type', MaterialTypeController::class);
    // Supplier master
    $router->resource('inventory/supplier-master', SupplierMasterController::class);
    // Daily batch
    $router->resource('inventory/daily-batch', Inventory\DailyBatchController::class);
    $router->post('inventory/daily-batch/save', 'Inventory\DailyBatchController@save');
    // Purchase order
    $router->resource('inventory/purchase-order', Inventory\PurchaseOrderController::class,  [
        'names' => [
            'index' => 'inventory-purchase-order-index'
        ]
    ]);
    $router->post('inventory/purchase-order/update-status/{id}', 'Inventory\PurchaseOrderController@updateStatus')
        ->name('inventory_report_purchase_order_update_status');
    $router->resource('inventory/purchase-order-detail', Inventory\PurchaseOrderDetailController::class);
    $router->get('inventory-report/purchase-order/{id}', 'Inventory\PurchaseOrderController@reportPurchaseDetail')
        ->name('inventory_report_purchase_order_detail');
    // Return order
    $router->resource('inventory/return', Inventory\ReturnOrderController::class);
    $router->post('/inventory/return/read-csv', 'Inventory\ReturnOrderController@readCsv');
    $router->post('/inventory/return/save', 'Inventory\ReturnOrderController@save');
    $router->post('/inventory/return/update', 'Inventory\ReturnOrderController@update');
    $router->get('/inventory/download-return', 'Inventory\ReturnOrderController@downloadFile')
        ->name('download_return');
    // Transfer in/out order
    $router->resource('inventory/transfer-order', Inventory\TransferOrderController::class);
    $router->post('/inventory/transfer-order/read-csv', 'Inventory\TransferOrderController@readCsv');
    $router->post('/inventory/transfer-order/save', 'Inventory\TransferOrderController@save');
    $router->post('/inventory/transfer-order/update', 'Inventory\TransferOrderController@update');
    $router->get('/inventory/download-transfer-order', 'Inventory\TransferOrderController@downloadFile')
        ->name('download_transfer_order');
    // Receive order
    $router->resource('inventory/receive-order', Inventory\ReceiveOrderController::class);
    $router->post('/inventory/receive-order/read-csv', 'Inventory\ReceiveOrderController@readCsv');
    $router->post('/inventory/receive-order/save', 'Inventory\ReceiveOrderController@save');
    $router->post('/inventory/receive-order/update', 'Inventory\ReceiveOrderController@update');
    $router->post('inventory/receive-order/confirm', 'Inventory\ReceiveOrderController@confirmStatus');
    $router->post('inventory/receive-order/compare-latest', 'Inventory\ReceiveOrderController@compareLatest');
    $router->get('/inventory/download-receive', 'Inventory\ReceiveOrderController@downloadFile')
        ->name('download_receive');
    //Recipe
    $router->resource('inventory/master-recipe', Inventory\MasterRecipeController::class);
    $router->post('/inventory/master-recipe/update-recipe-detail', 'Inventory\MasterRecipeController@updateRecipeDetail');
    $router->get('/inventory/master-recipe/get-recipe-detail/{id}', 'Inventory\MasterRecipeController@getRecipeDetailByRecipeId');
    //Wasted material
    $router->resource('inventory/wasted-material', Inventory\WastedMaterialController::class);
    $router->post('/inventory/wasted-material/save', 'Inventory\WastedMaterialController@save');

    // REPORT INVENTORY
    $router->get('/inventory-report/material-usage', 'Inventory\ViewMaterialUsageController@report')
        ->name('inventory-report-material-usage');
    $router->get('/inventory-report/current-stock', 'Inventory\ViewCurrentStockController@report')
        ->name('inventory-report-current-stock');
    $router->get('/inventory-report/daily-receiving', 'Inventory\ViewDailyReceivingController@report')
        ->name('inventory-report-daily-receiving');
    $router->get('/inventory-report/report-transaction', 'Inventory\ViewDailyReceivingController@reportTransaction')
        ->name('inventory-report-transaction');
    $router->get('/inventory-report/recipe-log', 'Inventory\RecipeLogController@report')
        ->name('inventory-report-recipe-log');
});

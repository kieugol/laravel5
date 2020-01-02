<?php

use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::get('/', function () {
    return 'IP Server(' . $_SERVER['SERVER_ADDR'] . ') - Laravel(' . app()->version() . ')' . "\r\n";
});

Route::group([
    'prefix' => 'sync'
], function (Router $router) {
    $router->post('/order', 'SyncOrderController@index')->name('sync_order');
    $router->post('/advance_order', 'SyncAdvanceOrderController@index');
    $router->post('/order-into-online', 'SyncOrderController@synOrderDeliveryForOnline');
});

Route::group([
    'prefix' => 'order-taker',
], function (Router $router) {
    $router->post('/upload', 'UploadOrderTakerController@create')->name('upload-order-taker');
    $router->get('/detail-by-code', 'UploadOrderTakerController@getOrderTakerByCode')->name('get-detail-by-code');
});

Route::group([
    'prefix' => 'advertise',
], function (Router $router) {
    $router->post('/led-menu/download', 'DownloadAdvertiseController@downloadLedMenu')->name('download-led-menu');
    $router->post('/monitor/download', 'DownloadAdvertiseController@downloadMonitor')->name('download-monitor');
});

// Material detail master
Route::group([
    'prefix'    => '/inventory',
    'namespace' => '\Inventory'
], function () {
    // Master material detail
    Route::group([
        'prefix' => '/material-detail',
    ], function (Router $router) {
        $router->get('/get-by-supplier/{id}', 'MasterMaterialDetailController@getBySupplier');
        $router->get('/get-by-keyword', 'MasterMaterialDetailController@getByKeyword');
        $router->get('/all', 'MasterMaterialDetailController@getAll');
        $router->get('/get-list-create-stock-opname', 'MasterMaterialDetailController@getListCreateStockOpname');
        $router->get('/get-by-barcode', 'MasterMaterialDetailController@getByBarcode');
    });

    // Master PCC
    Route::group([
        'prefix' => '/master-pcc',
    ], function (Router $router) {
        $router->get('get-by-period', 'MasterPccController@getByPeriod');
    });

    // Master purchase
    Route::group([
        'prefix' => '/purchase',
    ], function (Router $router) {
        $router->get('/get-by-id', 'PurchaseOrderController@getById');
        $router->get('/get-detail-by-purchase', 'PurchaseOrderController@getDetailByPurchaseId');
        $router->get('/', 'PurchaseOrderController@getList');
        $router->get('/{id}', 'PurchaseOrderController@getDetail');
        $router->put('/update-status/{id}', 'PurchaseOrderController@updateStatus');
    });

    // Stock Opname
    Route::group([
        'prefix' => '/stock-opname',
    ], function (Router $router) {
        $router->get('/get-total-usage-by-group-by-period', 'StockOpnameController@getTotalUsageByGroupByPeriod');
        $router->get('/', 'StockOpnameController@getList');
        $router->get('/all', 'StockOpnameController@getAll');
        $router->get('/{id}', 'StockOpnameController@getDetail');
        $router->post('/', 'StockOpnameController@create');
        $router->put('/{id}', 'StockOpnameController@update');
    });

    // Master Location
    Route::group([
        'prefix' => '/location',
    ], function (Router $router) {
        $router->get('/', 'LocationController@getAll');
    });

    // Master Uom
    Route::group([
        'prefix' => '/master-uom',
    ], function (Router $router) {
        $router->get('/', 'MasterUomController@list');
    });

    // Receive
    Route::group([
        'prefix' => '/receive-order',
    ], function (Router $router) {
        $router->post('/', 'ReceiveOrderController@create');
        $router->get('/get-material-detail', 'ReceiveOrderController@getReturnByReceiveId');
        $router->get('/', 'ReceiveOrderController@getList');
        $router->get('/{id}', 'ReceiveOrderController@getDetail');
        $router->post('/confirm', 'ReceiveOrderController@confirmStatus');
    });

    // Daily Batch
    Route::group([
        'prefix' => '/daily-batch',
    ], function (Router $router) {
        $router->get('/', 'DailyBatchController@getList');
        $router->get('/all', 'DailyBatchController@getAll');
        $router->get('/{id}', 'DailyBatchController@getDetail');
        $router->post('/', 'DailyBatchController@create');
        $router->put('/{id}', 'DailyBatchController@update');
        $router->get('/delete/{id}', 'DailyBatchController@delete');
    });

    // Recipe
    Route::group([
        'prefix' => '/recipe',
    ], function (Router $router) {
        $router->get('/', 'MasterRecipeController@getList');
        $router->get('/get-for-create-stock-opname', 'MasterRecipeController@getForCreateStockOpname');
        $router->get('/{id}', 'MasterRecipeController@getDetail');
        $router->put('/update/{id}', 'MasterRecipeController@updateRecipeDetail');
    });

    // Transfer order
    Route::group([
        'prefix' => '/transfer-order',
    ], function (Router $router) {
        $router->get('/get-material-detail', 'TransferOrderController@getMaterialDetailTransferOut');
    });

    // Sync data
    Route::group([
        'prefix' => '/sync-data',
    ], function (Router $router) {
        $router->post('/', 'SyncDataController@syncData');
        $router->post('/sync-new-version/create', 'SyncDataController@createNewSyncVersion');
    });
});

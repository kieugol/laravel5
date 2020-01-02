<?php

define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('SUPERADMIN', 'superadmin');
/*
 | define order type
----------------------------------------------------------------------------
*/
define('ORDER_TYPE_EATIN', 'I');
define('ORDER_TYPE_TAKEAWAY', 'C');
define('ORDER_TYPE_DELIVERY', 'D');
define('ORDER_TYPE', [
    ORDER_TYPE_EATIN    => 'Eat in',
    ORDER_TYPE_TAKEAWAY => 'Takeaway',
    ORDER_TYPE_DELIVERY => 'Delivery',
]);
define('ORDER_TYPE_DINEIN', 'I');

/*
 | define order status MMAP
----------------------------------------------------------------------------
*/
define('ORDER_STATUS_FINISHED', 1);
define('ORDER_STATUS_PARKING', 2);
define('ORDER_STATUS_ORDERED', 3);
define('ORDER_STATUS_CHECKOUT', 4);
define('ORDER_STATUS_CANCELED', 5);
define('ORDER_STATUS_COOKED', 6);
define('ORDER_STATUS_DELIVERING', 7);
define('ORDER_STATUS_EDITING', 8);
define('ORDER_STATUS_DELIVERED', 9);
define('ORDER_STATUS_COOKING', 10);

/*
 | define payment method
----------------------------------------------------------------------------
*/
define('PAYMENT_METHOD_CASH', 1);
define('PAYMENT_METHOD_OC', 'OC');
define('PAYMENT_METHOD_OUTLET_MEAL', 'MEALS-OUTLET');
define('PAYMENT_METHOD_TYPE_CASH', 'CASH');
define('PAYMENT_METHOD_TYPE_CARD', 'CARD');
define('PAYMENT_METHOD_TYPE_VOUCHER', 'VOUCHER');
define('PAYMENT_METHOD_TYPE_ECASH', 'E-CASH');

define('TAX_RATE', 1.1);
/*
 | define role
----------------------------------------------------------------------------
*/

define('ROLE_MANAGER', 1);
define('ROLE_DRIVER', 2);
define('ROLE_COOKER', 3);
define('ROLE_CASHIER', 4);

define('ROLE_ADMIN', 2);
define('ROLE_REPORTER', 3);

define('MAPPING_ROLE_ADMIN', [
    ROLE_MANAGER => ROLE_ADMIN,
    ROLE_DRIVER  => ROLE_REPORTER,
    ROLE_COOKER  => ROLE_REPORTER,
    ROLE_CASHIER => ROLE_REPORTER,
]);

define('CATEGORY_DONATION', 88);
define('CATEGORY_PIZZA', 58);
define('CATEGORY_DRINK', 71);

define('TYPE_CLOSE_TIME_1_DAY', 1);
define('TYPE_CLOSE_TIME_2_DAY', 2);

define('FTP_FOLDER_REPORT', '/var/www/posnew/pos1/data/report/');
/*
 | define order device
----------------------------------------------------------------------------
*/
define('ORDER_DEVICE_POS', 1);
define('ORDER_DEVICE_DINEIN', 2);
define('ORDER_DEVICE_ONLINE', 3);

define('LIMIT_NUMBER_SYNC_ORDER_STT', 5);

define('STATUS_DONE', 'Y');
define('STATUS_NOT_DONE', 'N');

define('API_POS_RESYNC_ORDER_STATUS', 'https://localhost:443/api_v1/cronjob/resync_order_into_callcenter');
define('API_INVENTORY', 'https://localhost:443/api_v1/inventory');
define('API_JUMPBOX', 'https://jumpbox.diqit.io/api/v1');
define('API_POSAPI', 'https://pos.diqit.io/api_v1');

define('SYNC_VERSION_TYPE_MENU', 'menu');
define('SYNC_VERSION_TYPE_PROMOTION', 'promotion');

define('FTP_HOST', '192.168.3.10');
define('FTP_PORT', '21');
define('FTP_TIMEOUT', 20);
define('FTP_EOD_USER_NAME', 'ftpphd');
define('FTP_EOD_PASSWORD', '3edcphd');
define('FTP_TRANSACTION_USER_NAME', 'ftpmcc');
define('FTP_TRANSACTION_PASSWORD', '123456');

define('KEY_NO', 'no');
define('KEY_NAME', 'name');

define('FTP_EOD_FOLDER_TRANSFER', [
    'Sun' => [
        KEY_NO   => '01',
        KEY_NAME => 'minggu',
    ], // Sun
    'Mon' => [
        KEY_NO   => '02',
        KEY_NAME => 'senin',
    ], // Mon
    'Tue' => [
        KEY_NO   => '03',
        KEY_NAME => 'selasa',
    ],// Tue
    'Wed' => [
        KEY_NO   => '04',
        KEY_NAME => 'rabu',
    ], // Web
    'Thu' => [
        KEY_NO   => '05',
        KEY_NAME => 'kamis',
    ], // Thurs
    'Fri' => [
        KEY_NO   => '06',
        KEY_NAME => 'jumat',
    ], // Fri
    'Sat' => [
        KEY_NO   => '07',
        KEY_NAME => 'sabtu',
    ], // Sat
]);

define('FTP_INVENTORY_FOLDER_REPORT', 'MCC');
define('PASSWORD_ZIP_DRS', '[pizzza]');

define('DEFAULT_OPENING_HOUR', 10);
define('DEFAULT_CLOSING_HOUR', 2);
define('DEFAULT_ADDITIONAL_HOURS', 3);

define('METHOD_POST', 'POST');
define('METHOD_GET', 'GET');
define('METHOD_PUT', 'PUT');

define('CSV_EXTENSION_NAME', 'csv');
define('EXCEL_EXTENSION_NAME', 'xls');

// Change minutes to seconds
define('ONE_MINUTE', 60);
define('THREE_MINUTES', 180);
define('TEN_MINUTES', 600);
define('THIRTY_MINUTES', 1800);
define('FOURTYFIVE_MINUTES', 2700);

define('ACTION_ADD_NEW', 1);
define('ACTION_UPDATED', 2);
define('ACTION_DELETED', 3);
define('ACTION_EDIT_ORDER', [
    ACTION_ADD_NEW => 'Added item',
    ACTION_UPDATED => 'Updated quantity',
    ACTION_DELETED => 'Deleted item',
]);

define('ORDER_TAKER', 'Order taker');
define('DOWNLOAD_ORDER_TAKER_SUCCESS', 1);
define('DOWNLOAD_ORDER_TAKER_FAILED', 3);
define('DOWNLOAD_ORDER_TAKER', [
    DOWNLOAD_ORDER_TAKER_SUCCESS => 'Success',
    DOWNLOAD_ORDER_TAKER_FAILED  => 'Failed',
]);
define('BASE_URL_ORDER_TAKER', 'https://posmanager.diqit.io/upload/order_taker');

define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);
define('STATUS_TRUE', true);
define('STATUS_FALSE', false);

/*
 | Define Partner Code
----------------------------------------------------------------------------
*/
define('GRAB', 'Grab');
define('GOJEK', 'Go-Jek');
define('OTHERS_CODE', '100');
define('GOJEK_CODE', '101');
define('GRAB_CODE', '102');

/*
 | Define Pizza Size
----------------------------------------------------------------------------
*/

define('EXTRA_CHEESE_REG', 'GC001');
define('EXTRA_CHEESE_JUMBO', 'GC002');

define('PERSONAL', 1);
define('REGULAR', 2);
define('JUMBO', 3);

/*
 | Define Name Queues
----------------------------------------------------------------------------
*/

define('QUEUE_SYNC_ORDER_FOR_JUMPBOX', 'sync_order_to_jumpbox');
define('QUEUE_SYNC_ORDER_FOR_ONLINE', 'sync_order_into_online_via_cc');
define('QUEUE_CALCULATION_CURRENT_STOCK', 'calculation_current_stock');
define('INVENTORY_QUEUE_INSERT_DATA_TO_POS', 'inventory_sync_master_data');


/*
 | Define Status
----------------------------------------------------------------------------
*/
define('SUCCESS', 1);
define('FAIL', 0);

/*
 | Define Upload Ads
----------------------------------------------------------------------------
*/
define('QUEUE_DOWNLOAD_ADS', "download-advertise");
define('ADS_STATUS_SUCCESS', 1);
define('ADS_STATUS_DOWNLOADING', 2);
define('ADS_STATUS_FAILED', 3);
define('ADS_STATUS', [
    ADS_STATUS_SUCCESS     => 'Succeed',
    ADS_STATUS_DOWNLOADING => 'Downloading',
    ADS_STATUS_FAILED      => 'Failed',
]);


define('BASE_URL_UPLOAD', env('APP_URL') .'/upload/');

/*
  |---------------------------------------------------------------------------------------------------------------------
  | inventory
  |---------------------------------------------------------------------------------------------------------------------
 */

define('MONTHS', [
    1  => 'January',
    2  => 'February',
    3  => 'March',
    4  => 'April',
    5  => 'May',
    6  => 'June',
    7  => 'July',
    8  => 'August',
    9  => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
]);


define('ACTION_CREATE', 1);
define('ACTION_UPDATE', 2);
define('ACTION_DELETE', 3);
define('ACTION_VIEW', 3);

define('INVENTORY_DIR_STOCK_OPNAME_CSV', DOCUMENT_ROOT . '/upload/inventory/stock-opname');
define('INVENTORY_DIR_PURCHASE_ORDER_CSV', DOCUMENT_ROOT . '/upload/inventory/purchase-order');
define('INVENTORY_DIR_RECEIVE_ORDER_CSV', DOCUMENT_ROOT . '/upload/inventory/receive-order');
define('INVENTORY_DIR_RETURN_ORDER_CSV', DOCUMENT_ROOT . '/upload/inventory/return-order');
define('INVENTORY_DIR_TRANSFER_ORDER_CSV', DOCUMENT_ROOT . '/upload/inventory/transfer-order');
define('INVENTORY_DIR_DAILY_SHEET_CSV', DOCUMENT_ROOT . '/upload/inventory/daily-sheet');
define('INVENTORY_DIR_DAILY_SHEET_DOWNLOAD_CSV', DOCUMENT_ROOT . '/upload/inventory/daily-sheet-download');

/**
 * Stock Opname order
 */
define('STOCK_OPNAME_STATUS_DRAFT', 1);
define('STOCK_OPNAME_STATUS_CONFIRMED', 2);
define('STOCK_OPNAME_STATUS', [
    STOCK_OPNAME_STATUS_DRAFT     => 'Draft',
    STOCK_OPNAME_STATUS_CONFIRMED => 'Confirmed'
]);

/**
 * Receive order
 */
define('RECEIVE_STATUS_DRAFT', 1);
define('RECEIVE_STATUS_CONFIRMED', 2);

/**
 * Supplier
 */
define('ALL', 'all');

/**
 * Transfer order
 */
define('TRANSFER_TYPE_IN', 1);
define('TRANSFER_TYPE_OUT', 2);
define('TRANSFER_TYPE', [
    TRANSFER_TYPE_IN  => 'In',
    TRANSFER_TYPE_OUT => 'Out',
]);

/**
 * Transaction status
 */
define('TRANSACTION_ORDER_STATUS_PENDING', 1);
define('TRANSACTION_ORDER_STATUS_APPROVED', 2);
define('TRANSACTION_ORDER_STATUS_REJECTED', 3);
define('TRANSACTION_ORDER_STATUS', [
    TRANSACTION_ORDER_STATUS_PENDING  => 'Pending',
    TRANSACTION_ORDER_STATUS_APPROVED => 'Approved',
    TRANSACTION_ORDER_STATUS_REJECTED => 'Rejected',
]);
define('SWITCH_TRANSACTION_RECEIVE_ORDER_STATUS', [
    TRANSACTION_ORDER_STATUS_PENDING  => 'Receive',
    TRANSACTION_ORDER_STATUS_APPROVED => 'Done',
    TRANSACTION_ORDER_STATUS_REJECTED => 'Rejected',
]);

/**
 * Define name transaction
 */
define('CSV_NAME_RECEIVE_TEMP', '-Rcv-temp.csv');
define('CSV_NAME_RETURN_TEMP', '-Rtn-temp.csv');
define('CSV_NAME_TRANSFER_COSYST_TEMP', '-Trf-temp.csv');
define('CSV_NAME_STOCK_OPNAME_TEMP', '-Opn-temp.csv');

define('CSV_NAME_RECEIVE', '-Rcv.csv');
define('CSV_NAME_RETURN', '-Rtn.csv');
define('CSV_NAME_TRANSFER_COSYST', '-Trf.csv');
define('CSV_NAME_STOCK_OPNAME', '-Opn.csv');
define('CSV_NAME_SALE_DATA', '-Sale.csv');

/**
 * Define period master PCC
 */
define('PERIOD_PCC_1', 1);
define('PERIOD_PCC_2', 2);
define('PERIOD_PCC_3', 3);
define('PERIOD_PCC_4', 4);

/**
 * Time for minus for previous day
 */
define('TIME_MINUS_PREVIOUS_DAY', '-6 hour');

/**
 * Define transaction type
 */
define('TRANSACTION_BEGINNING', 'beginning');

/**
 * Define view_daily_receive type
 */
define('DAILY_RECEIVE', 'RCV');
define('DAILY_RETURN', 'RTN');

define('INVENTORY_FOLDER_SYNC_MASTER', '/db/inventory/sync-data');
define('URL_SYNC_MASTER_TO_JUMPBOX', 'https://jumpbox.diqit.io/api/v1/sync-data');

/**
 * Define inventory_master_group
 */
define('GROUP_FOOD', 1);
define('GROUP_BEVERAGE', 2);

/**
 * Define inventory_location
 */
define('LOCATION_CHILLER_ID', 2);
define('LOCATION_BAR_ID', 3);

define('EXEC_CLEAR_CACHE_POS_API', 'rm -rf /var/www/posnew/pos1/application/cache/*');

define('COST_CONTROLER', 'Cost Controler');
define('ALL_PERMISSIONS', 'All permissions');
define('PERMISSION_USER_SETTING', 'User setting');
define('PERMISSION_REPORTER', 'Reporter');
define('PERMISSION_INVENTORY_REPORTER', 'Inventory Reporter');
define('PERMISSION_CONTROL_STOCKOPNAME', 'Control StockOpname');
define('PERMISSION_CONTROL_ALL_STOCKOPNAME', 'Control All StockOpname');

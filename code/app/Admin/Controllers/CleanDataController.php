<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/7/2018
 * Time: 4:19 PM
 */

namespace App\Admin\Controllers;

use App\Model\TablesTruncate;
use App\Repository\{
    AddressOthersRepository, AdminOperationLogRepository, Api\LogJobRepository, ApiAccessLogsRepository, CCAddressRepository, CCOrderResyncStatusRepository, CCOrderUpdateStatusRepository, CCSyncOrderDeliveryRepository, ComboGroupRepository, ComboMenuOptionRepository, ComboMenuRepository, ComboRepository, ComboVariantRepository, CustomerAddressRepository, CustomerLogLoyaltyRepository, CustomerRepository, DebugTableRepository, DeviceTokenRepository, EodHistoryRepository, FailedJobRepository, JobRepository, LogClientRepository, LogCurlRepository, MenuAutoCookRepository, MenuCategoryRepository, MenuCategoryRoleRepository, MenuGroupRepository, MenuRepository, MenuSkuRepository, MenuVariantRepository, OrderCouponRepository, OrderDeliveryRepository, OrderDetailRepository, OrderLogPrintRepository, OrderLogStatusRepository, OrderLogSyncRepository, OrderLogTimeRepository, OrderPaymentRepository, OrderRepository, OrderVoucherRepository, PartnerRepository, PaymentMethodRepository, PlucodeRepository, PointLogRepository, PrinterRepository, PromotionCouponRepository, PromotionRepository, ReportPaymentRepository, ShiftTransactionDetailRepository, ShiftTransactionRepository, SkuRepository, SyncLogRepository, SyncVersionRepository, TablesTruncateRepository, TrackSendOrderReportingRepository, UserCheckinRepository, UserDriverLocationRepository, UserDriverRepository, UserRepository, UserTokenRepository, VariantRepository, VoucherRepository, VoucherTypeRepository
};
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CleanDataController extends BaseController
{
    private $requests;
    private $tablesTruncateRepository;
    private $mappingRepository = [];
    
    public function __construct(Request $request,
        AdminOperationLogRepository $adminOperationLogRepository,
        ApiAccessLogsRepository $apiAccessLogsRepository,
        CustomerRepository $customerRepository,
        CustomerAddressRepository $customerAddressRepository,
        CustomerLogLoyaltyRepository $customerLogLoyaltyRepository,
        DebugTableRepository $debugTableRepository,
        DeviceTokenRepository $deviceTokenRepository,
        LogCurlRepository $logCurlRepository,
        OrderCouponRepository $orderCouponRepository,
        OrderDeliveryRepository $orderDeliveryRepository,
        OrderDetailRepository $orderDetailRepository,
        OrderLogPrintRepository $orderLogPrintRepository,
        OrderLogStatusRepository $orderLogStatusRepository,
        OrderLogSyncRepository $orderLogSyncRepository,
        OrderLogTimeRepository $orderLogTimeRepository,
        OrderPaymentRepository $orderPaymentRepository,
        OrderVoucherRepository $orderVoucherRepository,
        OrderRepository $orderRepository,
        PointLogRepository $pointLogRepository,
        ReportPaymentRepository $reportPaymentRepository,
        ShiftTransactionRepository $shiftTransactionRepository,
        ShiftTransactionDetailRepository $shiftTransactionDetailRepository,
        SyncLogRepository $syncLogRepository,
        UserRepository $userRepository,
        UserCheckinRepository $userCheckinRepository,
        UserDriverRepository $userDriverRepository,
        UserDriverLocationRepository $userDriverLocationRepository,
        UserTokenRepository $userTokenRepository,
        VoucherRepository $voucherRepository,
        EodHistoryRepository $eodHistoryRepository,
        AddressOthersRepository $addressOthersRepository,
        CCAddressRepository $CCAddressRepository,
        CCOrderUpdateStatusRepository $CCOrderUpdateStatusRepository,
        CCOrderResyncStatusRepository $CCOrderResyncStatusRepository,
        CCSyncOrderDeliveryRepository $CCSyncOrderDeliveryRepository,
        ComboRepository $comboRepository,
        ComboGroupRepository $comboGroupRepository,
        ComboMenuRepository $comboMenuRepository,
        ComboMenuOptionRepository $comboMenuOptionRepository,
        ComboVariantRepository $comboVariantRepository,
        FailedJobRepository $failedJobRepository,
        JobRepository $jobRepository,
        LogClientRepository $logClientRepository,
        LogJobRepository $logJobRepository,
        MenuRepository $menuRepository,
        MenuAutoCookRepository $menuAutoCookRepository,
        MenuCategoryRepository $menuCategoryRepository,
        MenuCategoryRoleRepository $menuCategoryRoleRepository,
        MenuGroupRepository $menuGroupRepository,
        MenuSkuRepository $menuSkuRepository,
        MenuVariantRepository $menuVariantRepository,
        PartnerRepository $partnerRepository,
        PaymentMethodRepository $paymentMethodRepository,
        PlucodeRepository $plucodeRepository,
        PrinterRepository $printerRepository,
        PromotionRepository $promotionRepository,
        PromotionCouponRepository $promotionCouponRepository,
        SkuRepository $skuRepository,
        SyncVersionRepository $syncVersionRepository,
        TrackSendOrderReportingRepository $trackSendOrderReportingRepository,
        VariantRepository $variantRepository,
        VoucherTypeRepository $voucherTypeRepository,
        TablesTruncateRepository $tablesTruncateRepository
)
    {
        parent::__construct();
        $this->requests = $request;
        $this->tablesTruncateRepository = $tablesTruncateRepository;
        $this->mappingRepository['admin_operation_log'] = $adminOperationLogRepository;
        $this->mappingRepository['apiaccesslogs'] = $apiAccessLogsRepository;
        $this->mappingRepository['customer'] = $customerRepository;
        $this->mappingRepository['customer_address'] = $customerAddressRepository;
        $this->mappingRepository['customer_log_loyalty'] = $customerLogLoyaltyRepository;
        $this->mappingRepository['debug_table'] = $debugTableRepository;
        $this->mappingRepository['device_token'] = $deviceTokenRepository;
        $this->mappingRepository['log_curl'] = $logCurlRepository;
        $this->mappingRepository['order_coupon'] = $orderCouponRepository;
        $this->mappingRepository['order_delivery'] = $orderDeliveryRepository;
        $this->mappingRepository['order_detail'] = $orderDetailRepository;
        $this->mappingRepository['order_log_print'] = $orderLogPrintRepository;
        $this->mappingRepository['order_log_status'] = $orderLogStatusRepository;
        $this->mappingRepository['order_log_sync'] = $orderLogSyncRepository;
        $this->mappingRepository['order_log_time'] = $orderLogTimeRepository;
        $this->mappingRepository['order_payment'] = $orderPaymentRepository;
        $this->mappingRepository['order_voucher'] = $orderVoucherRepository;
        $this->mappingRepository['order'] = $orderRepository;
        $this->mappingRepository['point_log'] = $pointLogRepository;
        $this->mappingRepository['report_payment'] = $reportPaymentRepository;
        $this->mappingRepository['shift_transaction'] = $shiftTransactionRepository;
        $this->mappingRepository['shift_transaction_detail'] = $shiftTransactionDetailRepository;
        $this->mappingRepository['sync_log'] = $syncLogRepository;
        $this->mappingRepository['user'] = $userRepository;
        $this->mappingRepository['user_checkin'] = $userCheckinRepository;
        $this->mappingRepository['user_driver'] = $userDriverRepository;
        $this->mappingRepository['user_driver_location'] = $userDriverLocationRepository;
        $this->mappingRepository['user_token'] = $userTokenRepository;
        $this->mappingRepository['voucher'] = $voucherRepository;
        $this->mappingRepository['eod_history'] = $eodHistoryRepository;
        $this->mappingRepository['address_others'] = $addressOthersRepository;
        $this->mappingRepository['cc_address'] = $CCAddressRepository;
        $this->mappingRepository['cc_order_update_status'] = $CCOrderUpdateStatusRepository;
        $this->mappingRepository['cc_order_resync_status'] = $CCOrderResyncStatusRepository;
        $this->mappingRepository['cc_sync_order_delivery'] = $CCSyncOrderDeliveryRepository;
        $this->mappingRepository['combo'] = $comboRepository;
        $this->mappingRepository['combo_group'] = $comboGroupRepository;
        $this->mappingRepository['combo_menu'] = $comboMenuRepository;
        $this->mappingRepository['combo_menu_option'] = $comboMenuOptionRepository;
        $this->mappingRepository['combo_variant'] = $comboVariantRepository;
        $this->mappingRepository['failed_jobs'] = $failedJobRepository;
        $this->mappingRepository['jobs'] = $jobRepository;
        $this->mappingRepository['log_client'] = $logClientRepository;
        $this->mappingRepository['log_jobs'] = $logJobRepository;
        $this->mappingRepository['menu'] = $menuRepository;
        $this->mappingRepository['menu_auto_cook'] = $menuAutoCookRepository;
        $this->mappingRepository['menu_category'] = $menuCategoryRepository;
        $this->mappingRepository['menu_category_role'] = $menuCategoryRoleRepository;
        $this->mappingRepository['menu_group'] = $menuGroupRepository;
        $this->mappingRepository['menu_sku'] = $menuSkuRepository;
        $this->mappingRepository['menu_variant'] = $menuVariantRepository;
        $this->mappingRepository['partner'] = $partnerRepository;
        $this->mappingRepository['payment_method'] = $paymentMethodRepository;
        $this->mappingRepository['plucode'] = $plucodeRepository;
        $this->mappingRepository['printer'] = $printerRepository;
        $this->mappingRepository['promotion'] = $promotionRepository;
        $this->mappingRepository['promotion_coupon'] = $promotionCouponRepository;
        $this->mappingRepository['sku'] = $skuRepository;
        $this->mappingRepository['sync_version'] = $syncVersionRepository;
        $this->mappingRepository['track_send_order_reporting'] = $trackSendOrderReportingRepository;
        $this->mappingRepository['variant'] = $variantRepository;
        $this->mappingRepository['voucher_type'] = $voucherTypeRepository;
    }

    public function index()
    {
        return Admin::content(function (Content $content) {
            $tables_truncate = $this->tablesTruncateRepository->getListTablesTruncate();
            foreach ($tables_truncate as &$table) {
                $table->is_empty = $this->mappingRepository[$table->table_name]->isEmptyData();
            }
            $content->header('CLEAN DATA');
            $content->description('Clean data in POS database');
            $content->body("<style>span .btn-group{display:none !important}</style>");
            $content->body($this->grid());
        });
    }

    public function grid()
    {
        $tables_truncate = $this->tablesTruncateRepository->getListTablesTruncate();
        foreach ($tables_truncate as &$table) {
            $table->is_empty = $this->mappingRepository[$table->table_name]->isEmptyData();
        }
        return Admin::grid(TablesTruncate::class, function (Grid $grid) use($tables_truncate) {
            $grid->disableExport();
            $grid->disablePagination();
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->tools(function ($tools) {
                $tools->append('<button class="btn btn-sm btn-danger" onclick="truncateTable()">Clean Data</button>');
            });

            $grid->column('id', "ID")->sortable();
            $grid->column('table_name')->display(function ($table_name){
                return  "<span style='color:blue'>$table_name</span>";
            });
            $grid->columnNames('Empty Data')->display(function() use ($tables_truncate){
                foreach ($tables_truncate as $table) {
                    if ($table->table_name == $this->table_name) {
                        $is_empty = $table->is_empty;
                    }
                }
                return ($is_empty == true) ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-close'></i>";
            });
            $grid->column('is_active', 'Active')->switch();

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like("table_name", "Table")->select(TablesTruncate::all()->pluck('table_name', 'table_name'));
            });
        });
    }

    protected function form() {
        return Admin::form(TablesTruncate::class, function (Form $form) {
            $form->switch("is_active", "Action");

        });
    }

    public function update($id)
    {
        return $this->form()->update($id);
    }

    public function truncateTable()
    {
        $tables_truncate = $this->requests->post('arr_table');
        ini_set('max_execution_time', 3000);
        try {
            foreach ($tables_truncate as $table){
                $active = $this->tablesTruncateRepository->checkIsActiveTable($table);
                if ($active) {
                    $this->mappingRepository[$table]->truncateData();
                }
            }
            $data['status'] = true;
            $data['message'] = 'Cleaned data Successfully';
    
            return response()->json($data, Response::HTTP_OK);
        }
        catch (\Exception $ex) {
            $data['status'] = false;
            $data['message'] = $ex->getMessage();
            Log::error('[Cleaned data]'.  $ex->getMessage() . ' At '. $ex->getFile() . '[' .  $ex->getLine() . ']', $this->requests->all());

            return response()->json($data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}

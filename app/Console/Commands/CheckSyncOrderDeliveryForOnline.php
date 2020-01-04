<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\{SyncOrderDeliveryRepository};
use App\Repository\Api\{SyncOrderRepository};
use App\Jobs\{SyncOrderIntoOnlineViaCallCenter};
use Illuminate\Support\Facades\Log;

class CheckSyncOrderDeliveryForOnline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkSyncOrderDeliveryForOnline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check order delivery was sent to online unsuccessfully and retry sending';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    private $syncOrderDeliveryRepo = null;

    public function __construct(SyncOrderDeliveryRepository $syncOrderDeliveryRepo,  SyncOrderRepository $orderRep)
    {
        parent::__construct();
        $this->syncOrderDeliveryRepo = $syncOrderDeliveryRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $listOrderReSyncOnline = $this->syncOrderDeliveryRepo->getListOrderIsNotSync();
        if (!empty($listOrderReSyncOnline)) {
            foreach ($listOrderReSyncOnline as $row) {
                //print_r("[Job] Re sync order delivery into Online via CallCenter: {$row->order_id}\r\n");
                Log::info('[Job] Re sync order delivery into Online via CallCenter', ['order_id' => $row->order_id]);
                SyncOrderIntoOnlineViaCallCenter::dispatch($row->order_id)->onQueue(QUEUE_SYNC_ORDER_FOR_ONLINE);
            }
        }
    }
}

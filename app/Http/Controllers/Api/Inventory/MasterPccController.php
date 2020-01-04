<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Repository\Inventory\{MasterPCCRepository};
use App\Admin\Controllers\BaseController;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\{Request, Response};

class MasterPccController extends BaseController
{
    use ModelForm;
    
    private $masterPCCRep = null;
    
    public function __construct(MasterPCCRepository $masterPCCRep)
    {
        parent::__construct();
        $this->masterPCCRep = $masterPCCRep;
    }
    
    public function getByPeriod(Request $request)
    {
        $month = $request->month ?? date('m');
        $year  = $request->year ?? date('Y');

        $data = $this->masterPCCRep->getAllByPeriod($month, $year);
        
        return response([
            'message' => '',
            'status'  => true,
            'data'    => $data,
        ], Response::HTTP_OK);
    }
}

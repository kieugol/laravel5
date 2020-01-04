<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
//use Encore\Admin\Controllers\Dashboard;
use App\Helpers\PosHelper;
use Encore\Admin\Facades\Admin;
//use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
//use Encore\Admin\Layout\Row;
use App\Repository\MenuRepository;
use App\Repository\EodHistoryRepository;
use App\Helpers\ConfigHelp;

class HomeController extends BaseController {

    private $mmenu = null;
    private $meodhistory = null;

    public function __construct(MenuRepository $mmenu, EodHistoryRepository $eodhistory) {
        parent::__construct();
        $this->mmenu = $mmenu;
        $this->meodhistory = $eodhistory;
    }

    public function index() {
        return Admin::content(function (Content $content) {

            $data = [];
            $data['total_menu'] = $this->mmenu->getModel()->count();
            $data['total_order'] = 2;
            $data['total_sku'] = 3;
            $result = array_merge($data, PosHelper::getDateTimeEOD());
            
            $content->header('DASHBOARD');
            $content->description('...');
            $content->body(view('module.home.index', $result));
        });
    }

}

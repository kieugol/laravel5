<?php

namespace App\Admin\Controllers;

use App\Model\EodHistory;
use App\Repository\OrderRepository;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use App\Helpers\PosHelper;
use App\Repository\EodHistoryRepository;
use Illuminate\Support\Facades\Log;


class EODController extends BaseController
{
    use ModelForm;

    private $eodRepo = null;
    private $orderRepo = null;
    
    public function __construct(EodHistoryRepository $eodRepo, OrderRepository $orderRep)
    {
        parent::__construct();
        $this->eodRepo = $eodRepo;
        $this->orderRepo = $orderRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('EOD Data');
            $content->description('List');
            $content->body("<style>.grid-row-update{display:none !important}</style>");
            $content->body($this->grid());
        });
    }

    public function update($id)
    {
        return $this->form()->update($id);
    }
    
    
    protected function form()
    {
        return Admin::form(EodHistory::class, function (Form $form) {

        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {

        return Admin::content(function (Content $content) use ($id) {

            $content->header('Revert order');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(EodHistory::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableCreateButton();
            $grid->disableExport();
            $grid->actions(function ($actions) {
                $actions->disableEdit();
            });

            $grid->model()->orderBy("id", "desc");
            $grid->column('id', 'No.');
            $grid->column('start_date','From');
            $grid->column('end_date', 'To');
            $grid->column('destination_ftp','Destination FTP');
            $grid->column('admin_users.name', 'Created By');
            $grid->column('created_at', 'Created At');
            $grid->column('is_sent_ftp','Send FTP')->display(function($is_sent_ftp) {
                return $is_sent_ftp ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-close text-danger"></i>';
            });
            $grid->column('path', 'Download File')->display(function($path){
                if (empty($path)) {
                    return '';
                }
                $path  = "'" . route('download_eod_csv') . "?path={$path}" . "&file_name=" . $this->file_name . "'";
                return '<button class="btn btn-xs btn-success" onclick="download_eod_csv('.$path.')"><i class="fa fa-download" aria-hidden="true"></i></i>&nbsp;&nbsp;Download</button>';
            });

            $grid->filter(function ($filter) {
                $filter->like('admin_users.name', 'Created By');
                $filter->gt('start_date', 'From')->datetime([
                    'format' => 'YYYY-MM-DD HH:mm:ss'
                ]);
                $filter->gt('end_date', 'To')->datetime([
                    'format' => 'YYYY-MM-DD HH:mm:ss',
                ]);
                $filter->between('created_date', 'Create At')->date([
                    'format' => 'YYYY-MM-DD HH:mm:ss'
                ]);
            });
        });
    }
    
    function downloadEodData(Request $request)
    {
        $path = $request->get('path', '');
        $file_name = $request->get('file_name', '');
        if (!empty($path) && !empty($file_name)) {
            $path_file_zip = $path . DIRECTORY_SEPARATOR . $file_name;
            $zip_file = glob($path_file_zip);
            if ($zip_file) {
                return response()->download($path_file_zip);
            } else {
                $files = glob($path . DIRECTORY_SEPARATOR . '*.csv');
                if ($files) {
                    \Zipper::make($file_name)->add($files)->close();
                    return response()->download($path_file_zip);
                }
            }
        }
        return null;
    }
}

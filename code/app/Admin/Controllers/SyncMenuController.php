<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Model\SyncVersion;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use App\Repository\SyncVersionRepository;
use Illuminate\Http\Request;

class SyncMenuController extends BaseController
{

    use ModelForm;

    private $msyncversion = null;

    public function __construct(SyncVersionRepository $msyncversion)
    {
        parent::__construct();
        $this->msyncversion = $msyncversion;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('List sync menu');
            $content->description('List');

            $content->body($this->grid());
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Sync Menu');
            $content->description('Create');

            $content->body($this->formcreate());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(SyncVersion::class, function (Grid $grid) {
            $grid->version();
            $grid->file_name();
            $grid->column('is_sync','Status')->display(function($is_sync){
                return $is_sync ?
                    '<i class="fa fa-check text-success"></i>&nbsp;&nbsp;Updated'
                    : '<i class="fa fa-close text-warning"></i>&nbsp;&nbsp;Not update';
            });
            $grid->column('id','Action')->display(function($id){
                $path  = "'" . route('resync_menu') . "?id={$id}" . "'";
                return '<button class="btn btn-sm btn-warning" onclick="resync_menu(this, '.$path.')"><i class="fa fa-undo" aria-hidden="true"></i></i>&nbsp;&nbsp;Resync</button>';
            });
            $grid->filter(function ($filter) {
                $filter->equal('version');
                $filter->equal('file_name');
            });
            $grid->disableActions();
        });
    }

    public function update($id)
    {
        return $this->formedit()->update($id);
    }

    protected function form()
    {
        $id = request()->input("id");
        if($id){
            return $this->formedit()->edit($id);
        }
        else{
            return $this->formcreate();
        }
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function formcreate()
    {
        return Admin::form(SyncVersion::class, function (Form $form) {

            $form->hidden('type')->value('menu');
            $form->text('version')->rules("required|unique:sync_version");
            $form->file('file_name')->move('menu_json');

        });
    }

    public function resyncMenu(Request $request)
    {
        $id = $request->input('id', 0);
        $result = $this->msyncversion->update(['is_sync' => 0], $id);
        if ($result) {
            $response = [
                'status' => 1,
                'message' => 'Resync menu successful'
            ];
        } else {
            $response = [
                'status' => 0,
                'message' => 'Resync menu error'
            ];
        }
        return response($response);
    }

}

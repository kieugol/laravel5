<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Model\User;
use App\Model\UserDriver;
use App\Model\Role;
use App\Repository\RoleRepository;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
//use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use App\Repository\UserDriverRepository;

class UserController extends BaseController
{

    use ModelForm;

    private $muserdriver = null;
    private $role_repository;

    public function __construct(UserDriverRepository $muserdriver, RoleRepository $role_repository)
    {
        parent::__construct();
        $this->muserdriver = $muserdriver;
        $this->role_repository = $role_repository;
        $this->outlet_code = ConfigHelp::get("outlet_code");
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Employee');
            $content->description('List');

            $content->body($this->grid());
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
        return response()->json([
            'status'  => false,
            'message' => trans('admin.deny')
        ]);
        
//        return Admin::content(function (Content $content) use ($id) {
////
////            $content->header('Employee');
////            $content->description('Edit');
////
////            $content->body($this->formedit()->edit($id));
////        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Employee');
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
        return Admin::grid(User::class, function (Grid $grid) {
            $grid->disableCreateButton();
            $grid->disableActions();
    
            $grid->model()->orderBy("id", "desc");
            $grid->avatar()->image(null, 50, 50);
            $grid->id()->sortable();
            $grid->username();
            $grid->name();
            $grid->email();
            $grid->phone();
            $grid->gender();
            $grid->column("role.name", "Permission");

            $grid->is_login("Is Login")->switch()->editable();
            $grid->is_active("Active")->switch()->editable();
            //$grid->is_login()->editable('select', array("0" => "OFF", "1" => "ON"));

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                    $filter->equal('role_id', "Permission")->select(Role::all()->pluck('name', 'id'));
            });
//            $grid->created_at();
//            $grid->updated_at();
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
        return Admin::form(User::class, function (Form $form) {
            $form->hidden('salt');

            $roles = $this->role_repository->getAllRoles();
            $prefix_code = '-'.$this->outlet_code;

            $form->select("role_id", "Role")->options($roles)->rules("required");
            $form->text('code')->rules("required")->value($prefix_code);
            $form->text('username')->rules("required");
            $form->password('password')->rules("");

            $form->image('avatar')->uniqueName();
            $form->text('name')->rules("required");
            $form->text('email')->rules('required|unique:user,email');
            $form->text('phone');
            $form->select("gender")->options(array("" => "", "M" => "Male", "F" => "Female"));
            $form->switch("is_active", "Is Active")->default(1);
            $form->display('created_date', 'Created At');
            $form->display('updated_date', 'Updated At');

            // callback before save
            $form->saving(function (Form $form) {
                $form->salt = md5(time());
                $form->password = sha1($form->password . $form->salt);
            });

            $form->saved(function (Form $form) {
                $model = $form->model();
                if ($model->role_id == ROLE_DRIVER) {
                    $insert = array(
                        "id" => $model->id,
                        'user_id' => $model->id,
                        'code' => $model->code,
                        "is_active" => 1,
                    );
                    $this->muserdriver->insert($insert);
                }
            });
        });
    }

    protected function formedit()
    {
        return Admin::form(User::class, function (Form $form) {

            $form->display("role.name", "Role");
            $form->text('code');
            $form->display('username');
            $form->text('password')->help("only type when you need change password")->attribute(array("value" => ""));

            $form->image('avatar')->uniqueName();
            $form->hidden('salt');
            $form->text('name')->rules("required");
            $form->text('email')->rules("required");
            $form->text('phone')->rules("required");
            $form->select("gender")->options(array("" => "", "M" => "Male", "F" => "Female"))->rules("required");
            $form->switch("is_active", "Is Active");
            $form->switch("is_login", "Is Login");

            $form->display('created_date', 'Created At');
            $form->display('updated_date', 'Updated At');

            // callback before save
            $form->saving(function (Form $form) {
                $model = $form->model();
                if (trim($form->password) != "") {
                    $form->salt = md5(time());
                    $form->password = sha1($form->password . $form->salt);
                }
                else{
                    $form->salt = $model->salt;
                    $form->password = $model->password;
                }
            });
        });
    }
    
    public function destroy($id)
    {
        return response()->json([
            'status'  => false,
            'message' => trans('admin.deny')
        ]);
    }
}

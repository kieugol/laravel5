<?php

namespace App\Admin\Controllers;

use App\Helpers\ConfigHelp;
use App\Repository\{RoleRepository, UserDriverRepository, UserRepository};
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class AdminUserController extends BaseController
{

    use ModelForm;
    
    private $roleRep;
    private $requests;
    private $userRep;
    private $userDriverRep;
    private $outletCode;
    private $customScript;
    
    public function __construct(
        Request $requests,
        RoleRepository $roleRep,
        UserRepository $userRep,
        UserDriverRepository $userDriverRep
    )
    {
        parent::__construct();
        $this->requests      = $requests;
        $this->roleRep       = $roleRep;
        $this->userRep       = $userRep;
        $this->userDriverRep = $userDriverRep;
        $this->outletCode    = ConfigHelp::get("outlet_code");
    
        $roleMapping = json_encode(MAPPING_ROLE_ADMIN, true);
        $this->customScript = <<<EOT
var mappingRole = {$roleMapping};
$('.role-order-taker').on('change', function () {
console.log(this)
   var roleAdmin = $(this).val();
  
   $('.role-admin').val(null);
   
   $('.role-admin').val(mappingRole[roleAdmin]);
});
EOT;
    }
    
    /**
     * Index interface.
     *
     * @return Content
     */
    public function index() {
        return Admin::content(function (Content $content) {
            $content->header("User Admin");
            $content->description(trans('admin.list'));
            $content->body($this->grid()->render());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id) {
        return Admin::content(function (Content $content) use ($id) {
            $content->header("User Admin");
            $content->description(trans('admin.edit'));
            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create() {
        return Admin::content(function (Content $content) {
            $content->header("User Admin");
            $content->description(trans('admin.create'));
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {
        return Administrator::grid(function (Grid $grid) {
            $grid->model()->where("username", "<>", "superadmin");
            $grid->id('ID')->sortable();
            $grid->username(trans('admin.username'));
            $grid->name(trans('admin.name'));
            $grid->roles(trans('admin.roles'))->pluck('name')->label();
            $grid->created_at(trans('admin.created_at'));
            $grid->updated_at(trans('admin.updated_at'));

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($actions->getKey() == 1) {
                    $actions->disableDelete();
                }
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
        });
    }
    
    /**
     * @param int $id
     *
     * @return Form
     */
    public function form($id = 0) {
        
        return Administrator::form(function (Form $form) use($id) {
            $roles       = $this->roleRep->getAllRoles();
            $userAdminId = $this->requests->method() == Request::METHOD_PUT ? $this->requests->get('id') : $id;
            $userAdmin   = Administrator::find($userAdminId);
            $user        = $this->userRep->findByAttributes(['username' => ($userAdmin->username ?? '')])->first();
            $userId      = $user->id ?? 0;
    
            $form->hidden('id');
            $form->text('username', trans('admin.username'))->rules('numeric')
                ->rules(["required", Rule::unique('admin_users')->ignore($userAdminId), Rule::unique('user')->ignore($userId)]);
            $form->text('name', trans('admin.name'))->rules('required');
            $form->image('avatar', trans('admin.avatar'));
            $form->text('email')
                ->rules(["required", Rule::unique('user')->ignore($userId)])
                ->value($user->email ?? null);
            $form->text('phone')
                ->rules(["required", Rule::unique('user')->ignore($userId)])
                ->value($user->phone ?? null);
            $form->select("gender")->options(array("" => "", "M" => "Male", "F" => "Female"))->value($user->gender ?? null);
            $form->password('password', trans('admin.password'))
                ->help($user ? trans('admin.remind_change_pass') : '')
                ->rules('confirmed')
                ->attribute(array("value" => ""));
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required_with:password')->value();
    
            $form->ignore(['password_confirmation']);
            $form->ignore(['email', 'phone', 'role_id', 'gender']); // for user order taker
    
            $form->multipleSelect('roles', 'posmanager Role')
                ->rules("required")
                ->options(Role::where("slug", "<>", "superadmin")
                ->get()
                ->pluck('name', 'id'));
    
            $form->select("role_id", "Order Taker Role")
                ->options($roles)
                ->rules("required")
                ->value($user->role_id ?? 0);
            
            $form->saving(function (Form $form) {
                $newPass = trim($this->requests->get('password'));
                if ($newPass != '') {
                    $form->password = bcrypt($newPass);
                } else {
                    $form->password = $form->model()->getAttribute('password');
                }
            });
    
            $form->saved(function () use ($user) {
                $this->createUserForOrderTaker($user);
            });
        });
    }
    
    protected function createUserForOrderTaker($userDetail)
    {
        $params             = $this->requests->all();
        $params['code']     = trim($params['username']) . '-' . $this->outletCode;
        
        if (!empty($params['password'])) {
            $salt               = md5(time());
            $params['salt']     = $salt;
            $params['password'] = sha1($params['password'] . $salt);
        } else {
            $params['password'] = $userDetail->password;
        }
    
        DB::beginTransaction();
        
        if (empty($userDetail)) {
            $user = $this->userRep->create($params);
            if ($user->role_id == ROLE_DRIVER) {
                $this->userDriverRep->insert([
                    'id' => $user->id,
                    'user_id'   => $user->id,
                    'code'      => $user->code,
                    'is_active' => STATUS_ACTIVE,
                ]);
            }
        } else {
            $this->userRep->update($params, $userDetail->id);
            $userDriver = $this->userDriverRep->findByAttributes(['user_id' => $userDetail->id])->first();
            if ($userDriver) {
                $this->userDriverRep->update(['code' => $params['code'],], $userDriver->id);
            }
        }
        
        DB::commit();
    }
    
    public function destroy($id)
    {
        $userAdmin = Administrator::find($id);
        if ($this->form()->destroy($id)) {
            $userName   = $userAdmin->username;
            $userDetail = $this->userRep->findByAttributes(['username' => $userName])->first();
            
            if ($userDetail) {
                $this->userRep->destroy($userDetail->id);
                if ($userDetail->role_id == ROLE_DRIVER) {
                    $this->userDriverRep->getModel()->where('user_id', $userDetail->id)->delete();
                }
            }
            
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }
    
    protected function mappingRole()
    {
        $adminRole = Role::where("slug", "<>", "superadmin")
            ->get()
            ->pluck('name', 'id');
        
    }
}

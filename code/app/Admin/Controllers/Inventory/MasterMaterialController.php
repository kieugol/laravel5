<?php

namespace App\Admin\Controllers\Inventory;

use App\Model\Inventory\MasterMaterial;
use App\Repository\Inventory\{MasterMaterialRepository};
use App\Admin\Controllers\BaseController;
use Encore\Admin\{Grid, Form};
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

class MasterMaterialController extends BaseController
{
    use ModelForm;

    private $materialRep = null;

    public function __construct(MasterMaterialRepository $materialRep)
    {
        parent::__construct();
        $this->materialRep = $materialRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {

    }

    public function update($id)
    {
        return [];
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {

    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function formCreate()
    {
        return null;
    }

    public function destroy($id)
    {
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(MasterMaterial::class, function (Grid $grid) {

        });
    }
}

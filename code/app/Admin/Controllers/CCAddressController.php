<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 11/9/2018
 * Time: 9:49 AM
 */

namespace App\Admin\Controllers;


use App\Model\CCAddress;
use App\Repository\CCAddressRepository;
use App\Rules\CheckIsCsvFile;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Validator;

class CCAddressController extends BaseController
{
    use ModelForm;
    const COLUMN_NAME = 0;
    const OUTLET_CODE = 1;
    const ZONE = 2;
    const BUILDING = 3;
    const STREET = 4;
    private $cc_address_repository;

    public function __construct(CCAddressRepository $cc_address_repository)
    {
        $this->cc_address_repository = $cc_address_repository;
    }

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('CallCenter Address');
            $content->description('Show address for podding');

            $content->body($this->grid());
        });
    }

    protected function grid()
    {
        return Admin::grid(CCAddress::class, function (Grid $grid) {
            $url = route('import_cc_address');
            $grid->id('ID')->sortable();
            $grid->outlet_code('Outlet Code')->sortable();
            $grid->building('Building')->sortable();
            $grid->street('Street')->sortable();
            $grid->district('District')->sortable();
            $grid->zone('Zone')->sortable();
            $grid->search('Search');
            $grid->tools(function ($tools) use ($url) {
                $tools->append("<a href='$url' class='btn btn-sm btn-twitter'><i class='fa fa-download'></i> Import</a>");
            });

            $grid->filter(function ($filter) {
                // Sets the range query for the created_at field
                $filter->like('outlet_code', 'Outlet Code');
                $filter->like('zone', 'Zone');
                $filter->like('search', 'Search');
            });
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Callcenter Address');
            $content->description('Show');

            $content->body($this->form()->view($id));
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Callcenter Address');
            $content->description('Create');

            $content->body($this->form());
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('SKU');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
        });
    }

    protected function form()
    {
        return Admin::form(CCAddress::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->text("outlet_code", "Outlet Code")->rules("required");
            $form->text("building", "Building")->rules("required");
            $form->text("street", "Street")->rules("required");
            $form->text("district", "District")->rules("required");
            $form->text("zone", "Zone")->rules("required");
            $form->text("search", "Search")->rules("required");

        });
    }

    public function importCCAddress(Request $request)
    {
        return view('module.ccaddress.import_cc_address');
    }

    public function saveAddress(Request $request)
    {
        $this->validate($request, [
            'file'      => new CheckIsCsvFile($request)
        ]);
        $file     = $request->file('file');
        $tempPath = $file->getRealPath();
        $data     = array_map('str_getcsv', file($tempPath));
        unset($data[self::COLUMN_NAME]);  // remove column name row
        $data_insert = [];
        foreach ($data as $item) {
            $row = [
                'outlet_code'          => $item[self::OUTLET_CODE],
                'building'             => $item[self::BUILDING],
                'street'               => $item[self::STREET],
                'district'             => '',
                'city_id'              => '',
                'zone'                 => $item[self::ZONE],
                'search'               => $item[self::STREET],
                'data_upload_id'       => '',
                'data_source_id'       => '',
                'outlet_id2'           => '',
                'outlet_id3'           => '',
                'outlet_id4'           => '',
                'sync'                 => '',
                'podding_level_status' => ''
            ];
            if (!empty($item[self::OUTLET_CODE])) {
                $data_insert[] = $row;
            }
        }
        $this->cc_address_repository->insert($data_insert);
        return response([
            'status' => true,
            'message'=> 'Import address successfully .',
            'data'   => ''
        ], Response::HTTP_OK);
    }

}
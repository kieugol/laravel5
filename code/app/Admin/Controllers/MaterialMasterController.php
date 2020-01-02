<?php

namespace App\Admin\Controllers;


use App\Admin\Controllers\BaseController;
use App\Model\Uom;
use Illuminate\Http\Request;

class MaterialMasterController extends BaseController
{
    /**
     * Index interface.
     */
    public function index()
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/material-master');
        $response = $request->getBody()->getContents();
        $data = json_decode($response);


        $request_mat_group = $client->get(config('admin.inventory_api_url') . '/material-group');
        $response_mat_group = $request_mat_group->getBody()->getContents();
        $data_mat_group = json_decode($response_mat_group);
        $data_init['material_groups'] = $data_mat_group->data;

        $request_mat_type = $client->get(config('admin.inventory_api_url') . '/material-type');
        $response_mat_type = $request_mat_type->getBody()->getContents();
        $data_mat_type = json_decode($response_mat_type);
        $data_init['material_types'] = $data_mat_type->data;

        $data_init['uoms'] = Uom::all();


        return view('inventory.material_master.index', $data_init)->with('material_masters', $data->data);
    }

    /**
     * Create interface.
     */
    public function create()
    {
        $client = new \GuzzleHttp\Client();
        $request_mat_group = $client->get(config('admin.inventory_api_url') . '/material-group');
        $response_mat_group = $request_mat_group->getBody()->getContents();
        $data_mat_group = json_decode($response_mat_group);
        $data['material_groups'] = $data_mat_group->data;


        $request_mat_type = $client->get(config('admin.inventory_api_url') . '/material-type');
        $response_mat_type = $request_mat_type->getBody()->getContents();
        $data_mat_type = json_decode($response_mat_type);
        $data['material_types'] = $data_mat_type->data;

        $data['uoms'] = Uom::all();

        return view('inventory.material_master.create', $data);
    }

    /**
     * Storing Data.
     */
    public function store(Request $request)
    {
        $this->mMasterValidationRules($request);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', config('admin.inventory_api_url') . '/material-master', [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/material-master');
    }

    /**
     * Edit interface.
     */
    public function edit($id)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/material-master/'. $id);
        $response = $request->getBody()->getContents();
        $data = json_decode($response);


        $request_mat_group = $client->get(config('admin.inventory_api_url') . '/material-group');
        $response_mat_group = $request_mat_group->getBody()->getContents();
        $data_mat_group = json_decode($response_mat_group);
        $data_init['material_groups'] = $data_mat_group->data;

        $request_mat_type = $client->get(config('admin.inventory_api_url') . '/material-type');
        $response_mat_type = $request_mat_type->getBody()->getContents();
        $data_mat_type = json_decode($response_mat_type);
        $data_init['material_types'] = $data_mat_type->data;

        $data_init['uoms'] = Uom::all();


        return view('inventory.material_master.edit', $data_init)->with('material_master', $data->data);
    }

    /**
     * Updating Data.
     */
    public function update(Request $request, $id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('PUT', config('admin.inventory_api_url') . '/material-master/'. $id, [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/material-master');
    }

    /**
     * Delete Data.
     */
    public function destroy($id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('DELETE', config('admin.inventory_api_url') . '/material-master/'. $id);

        return response()->json([
            'message' => 'Item successfully deleted'
        ], 200);
    }

    /**
     * Form Validation Rules of Material Master.
     */
    public function mMasterValidationRules($request){
        $rules = [
            'material_code' => 'required',
            'material_group_id' => 'required',
            'material_type_id' => 'required',
            'shortname' => 'required',
            'longname' => 'required',
            'current_price' => 'required',
            'current_qty' => 'required',
            'uom_id' => 'required'
        ];
        $this->validate($request, $rules);
    }

}

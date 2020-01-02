<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use Illuminate\Http\Request;

class MaterialGroupController extends BaseController
{

    /**
     * Index interface.
     */
    public function index()
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/material-group');
        $response = $request->getBody()->getContents();
        $data = json_decode($response);

        return view('inventory.material_group.index')->with('groups', $data->data);
    }

    /**
     * Create interface.
     */
    public function create()
    {
        return view('inventory.material_group.create');
    }

    /**
     * Storing Data.
     */
    public function store(Request $request)
    {
        $this->mGroupValidationRules($request);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', config('admin.inventory_api_url') . '/material-group', [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/material-group');
    }

    /**
     * Edit interface.
     */
    public function edit($id)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/material-group/'. $id);
        $response = $request->getBody()->getContents();
        $data = json_decode($response);

        return view('inventory.material_group.edit')->with('group', $data->data);
    }

    /**
     * Updating Data.
     */
    public function update(Request $request, $id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('PUT', config('admin.inventory_api_url') . '/material-group/'. $id, [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/material-group');
    }

    /**
     * Deleting Data.
     */
    public function destroy($id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('DELETE', config('admin.inventory_api_url') . '/material-group/'. $id);

        return response()->json([
            'message' => 'Item successfully deleted'
        ], 200);
    }

    /**
     * Form Validation Rules of Material Group.
     */
    public function mGroupValidationRules($request){
        $rules = [
            'group_id' => 'required',
            'group_name' => 'required'
        ];
        $this->validate($request, $rules);
    }

}

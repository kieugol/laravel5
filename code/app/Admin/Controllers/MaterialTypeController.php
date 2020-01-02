<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
use Illuminate\Http\Request;

class MaterialTypeController extends BaseController
{

    /**
     * Index interface.
     */
    public function index()
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/material-type');
        $response = $request->getBody()->getContents();
        $data = json_decode($response);

        return view('inventory.material_type.index')->with('types', $data->data);
    }

    /**
     * Create interface.
     */
    public function create()
    {
        return view('inventory.material_type.create');
    }

    /**
     * Storing Data.
     */
    public function store(Request $request)
    {
        $this->mTypeValidationRules($request);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', config('admin.inventory_api_url') . '/material-type', [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/material-type');
    }

    /**
     * Edit interface.
     */
    public function edit($id)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/material-type/'. $id);
        $response = $request->getBody()->getContents();
        $data = json_decode($response);

        return view('inventory.material_type.edit')->with('type', $data->data);
    }

    /**
     * Updating Data.
     */
    public function update(Request $request, $id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('PUT', config('admin.inventory_api_url') . '/material-type/'. $id, [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/material-type');
    }

    /**
     * Deleting Data.
     */
    public function destroy($id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('DELETE', config('admin.inventory_api_url') . '/material-type/'. $id);

        return response()->json([
            'message' => 'Item successfully deleted'
        ], 200);
    }

    /**
     * Form Validation Rules of Material Type.
     */
    public function mTypeValidationRules($request){
        $rules = [
            'type_id' => 'required',
            'type_name' => 'required'
        ];
        $this->validate($request, $rules);
    }

}

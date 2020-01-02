<?php

namespace App\Admin\Controllers;


use App\Admin\Controllers\BaseController;
use Illuminate\Http\Request;

class SupplierMasterController extends BaseController
{

    /**
     * Index interface.
     */
    public function index()
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/supplier-master');
        $response = $request->getBody()->getContents();
        $data = json_decode($response);

        return view('inventory.supplier_master.index')->with('supplier_masters', $data->data);
    }

    /**
     * Create interface.
     */
    public function create()
    {
        return view('inventory.supplier_master.create');
    }

    /**
     * Storing Data.
     */
    public function store(Request $request)
    {
        $this->sMasterValidationRules($request);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', config('admin.inventory_api_url') . '/supplier-master', [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/supplier-master');
    }

    /**
     * Edit interface.
     */
    public function edit($id)
    {
        $client = new \GuzzleHttp\Client();
        $request = $client->get(config('admin.inventory_api_url') . '/supplier-master/'. $id);
        $response = $request->getBody()->getContents();
        $data = json_decode($response);

        return view('inventory.supplier_master.edit')->with('supplier_master', $data->data);
    }

    /**
     * Updating Data.
     */
    public function update(Request $request, $id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('PUT', config('admin.inventory_api_url') . '/supplier-master/'. $id, [
            'form_params' => $request->all()
        ]);

        return redirect('admin/inventory/supplier-master');
    }

    /**
     * Deleting Data.
     */
    public function destroy($id)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('DELETE', config('admin.inventory_api_url') . '/supplier-master/'. $id);

        return response()->json([
            'message' => 'Item successfully deleted'
        ], 200);
    }

    /**
     * Form Validation Rules of Supplier Master.
     */
    public function sMasterValidationRules($request){
        $rules = [
            'supplier_code' => 'required',
            'supplier_name' => 'required',
            'address' => 'required',
            'phone_number' => 'required'
        ];
        $this->validate($request, $rules);
    }

}

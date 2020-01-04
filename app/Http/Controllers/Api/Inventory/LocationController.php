<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 3/4/2019
 * Time: 11:21 AM
 */

namespace App\Http\Controllers\Api\Inventory;


use App\Admin\Controllers\BaseController;
use App\Repository\Inventory\LocationRepository;
use Illuminate\Http\Response;

class LocationController extends BaseController
{

    private $location_repository;
    public function __construct(LocationRepository $location_repository)
    {
        parent::__construct();
        $this->location_repository = $location_repository;
    }

    public function getAll()
    {
        return response([
            'message' => '',
            'status'  => true,
            'data'    => $this->location_repository->all('ASC'),
        ], Response::HTTP_OK);
    }
}

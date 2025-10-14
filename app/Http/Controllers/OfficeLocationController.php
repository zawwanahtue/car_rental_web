<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\OfficeLocationService;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    protected $helper;
    protected $officeLocationService;
    public function __construct(Helper $helper, OfficeLocationService $officeLocationService)
    {
        $this->helper = $helper;
        $this->officeLocationService = $officeLocationService;
    }

    public function getAllOfficeLocations() {
        $officeLocations = $this->officeLocationService->getAllOfficeLocations();
        if (is_null($officeLocations)) {
            return $this->helper->PostMan(null, 404, "No office locations found");
        } else {
            return $this->helper->PostMan($officeLocations, 200, "Office locations found");
        }
    }

    public function createOfficeLocation(Request $request) {
        $rule = [
            'location_name' => 'required|string|max:255',
            'longitude' => 'required|numberic',
            'latitude' => 'required|numberic',
        ];
        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate)) {
            $data = $request->all();
            $response = $this->officeLocationService->createOfficeLocation($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Office Location Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateOfficeLocation(Request $request, $id) {
        $rule = [
            'location_name' => 'required|string|max:255',
            'longitude' => 'required|numberic',
            'latitude' => 'required|numberic',
        ];
        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate)) {
            $data['office_location_id'] = $id;
            $data = $request->all();
            $response = $this->officeLocationService->updateOfficeLocation($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Office Location Successfully Updated");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteOfficeLocation($id) {
        $response = $this->officeLocationService->deleteOfficeLocation($id);
        if (is_null($response)) {
            return $this->helper->PostMan(null, 201, "Office Location Successfully Deleted");
        } else {
            return $this->helper->PostMan(null, 500, $response);
        }
    }
}
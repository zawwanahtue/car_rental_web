<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Services\CommonService;

class LocationController extends Controller
{
    protected $locationService;
    protected $helper;
    protected $commonService;
    public function __construct(LocationService $locationService, Helper $helper, CommonService $commonService)
    {
        $this->locationService = $locationService;
        $this->helper = $helper;
        $this->commonService = $commonService;
    }

    // Location Type
    public function getAllLocationTypes()
    {
        $locations = $this->locationService->getAllLocationTypes();
        if(is_null($locations))
        {
            return $this->helper->PostMan(null, 500, "Locations not found");
        }
        return $this->helper->PostMan($locations, 200, "Successfully retrieved locations");
    }

    public function createLocationType(Request $request)
    {
        $rules = [
            'type_name' => 'required|string|max:255|unique:location_types,type_name',
            'description' => 'required|string|max:500',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data = $request->all();
            $response = $this->locationService->createLocationType($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Location Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateLocationType(Request $request, $id)
    {
        $rules = [
            'type_name' => 'sometimes|required|string|max:255|unique:location_types,type_name,'.$id.',location_type_id',
            'description' => 'sometimes|required|string|max:500',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data = $request->all();
            $data['location_type_id'] = $id;
            $response = $this->locationService->updateLocationType($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 200, "Location Type Successfully Updated");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteLocationType($id)
    {
        $response = $this->locationService->deleteLocationType($id);
        if (is_null($response)) {
            return $this->helper->PostMan(null, 200, "Location Type Successfully Deleted");
        } else {
            return $this->helper->PostMan(null, 500, $response);
        }
    }

    // Location
    public function getAllLocations()
    {
        $locations = $this->locationService->getAllLocations();
        if(is_null($locations))
        {
            return $this->helper->PostMan(null, 500, "Locations not found");
        }
        return $this->helper->PostMan($locations, 200, "Successfully retrieved locations");
    }

    public function createLocation(Request $request)
    {
        $rules = [
            'location_name' => 'required|string|max:255|unique:locations,location_name',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_type_id' => 'required|exists:location_types,location_type_id',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data = $request->all();
            $response = $this->locationService->createLocation($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Location Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateLocation(Request $request, $id)
    {
        $rules = [
            'location_name' => 'sometimes|required|string|max:255|unique:locations,location_name,'.$id.',location_id',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'location_type_id' => 'sometimes|required|exists:location_types,location_type_id',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data = $request->all();
            $data['location_id'] = $id;
            $response = $this->locationService->updateLocation($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 200, "Location Successfully Updated");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteLocation($id)
    {
        $isRelated = $this->commonService->ForeignKeyIsExit("user_preferences", "preferred_location_id", $id);
        $isRelated1 = $this->commonService->ForeignKeyIsExit("bookings", "pickup_location_id", $id);
        $isRelated2 = $this->commonService->ForeignKeyIsExit("bookings", "delivery_location_id", $id);
        if ($isRelated && $isRelated1 && $isRelated2) {
            return $this->helper->PostMan(null, 500, "Location have used. Cannot delete.");
        }
        $response = $this->locationService->deleteLocation($id);
        if (is_null($response)) {
            return $this->helper->PostMan(null, 200, "Location Successfully Deleted");
        } else {
            return $this->helper->PostMan(null, 500, $response);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\UserPreferenceLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceLocationController extends Controller
{
    protected $service;
    protected $helper;

    public function __construct(UserPreferenceLocationService $service, Helper $helper)
    {
        $this->service = $service;
        $this->helper = $helper;
    }

    public function store(Request $request)
    {
        $rules = [
            'location_name' => 'required|string|max:255',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
        ];

        $validate = $this->helper->validate($request, $rules);
        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $userId = auth('sanctum')->user()->user_id;
        $result = $this->service->addLocation($userId, $request->all());

        if (is_string($result)) {
            return $this->helper->PostMan(null, 400, $result);
        }

        return $this->helper->PostMan(null, 201, "Location saved");
    }

    public function index()
    {
        $userId = Auth::user()->user_id;
        $locations = $this->service->getLocations($userId);
        return $this->helper->PostMan($locations, 200, "Latest locations");
    }
}
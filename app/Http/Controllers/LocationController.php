<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class LocationController extends Controller
{
    protected $locationService;
    protected $helper;
    public function __construct(LocationService $locationService, Helper $helper)
    {
        $this->locationService = $locationService;
        $this->helper = $helper;
    }
    
    public function getAllLocations()
    {
        $locations = $this->locationService->getAllLocations();
        if(is_null($locations))
        {
            return $this->helper->PostMan(null, 500, "Locations not found");
        }
        return $this->helper->PostMan($locations, 200, "Successfully retrieved locations");
    }

    public function addLocation()
    {
        
    }
}

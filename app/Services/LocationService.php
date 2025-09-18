<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class LocationService
{
    public function getAllLocations()
    {
        $locations = DB::table("locations")
        ->get();

        return $locations;
    }
}
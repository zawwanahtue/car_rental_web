<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LocationService
{
    // Location Type
    public function getAllLocationTypes()
    {
        $locations = DB::table("location_types")
        ->get();
        return $locations;
    }

    public function createLocationType($data)
    {
        $response = DB::table('location_types')->insert($data);
        if (!$response) {
            return "Failed to create location type.";
        } else {
            return null;
        }
    }

    public function updateLocationType($data)
    {
        $response = DB::table('location_types')->where('location_type_id', $data['location_type_id'])->update($data);
        if (!$response) {
            return "Location type not found.";
        } else {
            return null;
        }
    }

    public function deleteLocationType($id)
    {
        $isRelated = DB::table('locations')->where('location_type_id', $id)->exists();
        if ($isRelated) {
            return "Location type have used. Cannot delete.";
        }
        $response = DB::table('location_types')->where('location_type_id', $id)->delete();
        if (!$response) {
            return "Location type not found.";
        } else {
            return null;
        }
    }

    // Location
    public function getAllLocations()
    {
        $locations = DB::table("locations as l")
        ->join("location_types as lt", "l.location_type_id", "=", "lt.location_type_id")
        ->select(
            'l.location_id',
            'l.location_name',
            'l.latitude',
            'l.longitude',
            'lt.location_type_id',
            'lt.type_name as location_type_name',
        )
        ->get();
        return $locations;
    }

    public function createLocation($data)
    {
        $response = DB::table('locations')->insert($data);
        if (!$response) {
            return "Failed to create location.";
        } else {
            return null;
        }
    }

    public function updateLocation($data)
    {
        $response = DB::table('locations')->where('location_id', $data['location_id'])->update($data);
        if (!$response) {
            return "Location not found.";
        } else {
            return null;
        }
    }

    public function deleteLocation($id)
    {
        $response = DB::table('locations')->where('location_id', $id)->delete();
        if (!$response) {
            return "Location not found.";
        } else {
            return null;
        }
    }
}
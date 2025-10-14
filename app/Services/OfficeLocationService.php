<?php 

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OfficeLocationService{

    public function getAllOfficeLocations(){
        $officeLocations = DB::table('office_locations')->get();
        if (!$officeLocations) {
            return "No office locations found.";
        }
        return $officeLocations;
    }

    public function createOfficeLocation(array $data){
        $response = DB::table('office_locations')->insert($data);
        if (!$response) {
            return "Failed to create office location.";
        }
        return null;
    }

    public function updateOfficeLocation(array $data){
        $data['updated_at'] = now();
        $response = DB::table('office_locations')->where('office_location_id', $data['office_location_id'])->update($data);
        if (!$response) {
            return "Failed to update office location.";
        }
        return null;
    }

    public function deleteOfficeLocation($id){
        $response = DB::table('office_locations')->where('office_location_id', $id)->delete();
        if (!$response) {
            return "Failed to delete office location.";
        }
        return null;
    }
}
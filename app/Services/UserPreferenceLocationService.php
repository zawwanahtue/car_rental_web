<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UserPreferenceLocationService
{
    public function addLocation($userId, $data)
    {
        $lat = $data['latitude'];
        $lng = $data['longitude'];
        $name = $data['location_name'];

        DB::transaction(function () use ($userId, $name, $lat, $lng) {

            // 1. Check if exact same lat + lng exists
            $exists = DB::table('user_preference_locations')
                ->where('user_id', $userId)
                ->where('latitude', $lat)
                ->where('longitude', $lng)
                ->exists();

            if ($exists) {
                // Just bump updated_at → moves to top
                DB::table('user_preference_locations')
                    ->where('user_id', $userId)
                    ->where('latitude', $lat)
                    ->where('longitude', $lng)
                    ->update([
                        'location_name' => $name,
                        'updated_at'    => now()
                    ]);
            } else {
                // INSERT NEW LOCATION
                DB::table('user_preference_locations')->insert([
                    'user_id'       => $userId,
                    'location_name' => $name,
                    'latitude'      => $lat,
                    'longitude'     => $lng,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 2. DELETE OLDEST beyond 10 (by updated_at)
            DB::statement("
                DELETE FROM user_preference_locations 
                WHERE preference_location_id IN (
                    SELECT * FROM (
                        SELECT preference_location_id 
                        FROM user_preference_locations 
                        WHERE user_id = ? 
                        ORDER BY updated_at DESC 
                        LIMIT 999 OFFSET 10
                    ) AS tmp
                )
            ", [$userId]);
        });

        // Return the location (new or updated)
        return DB::table('user_preference_locations')
            ->where('user_id', $userId)
            ->where('latitude', $lat)
            ->where('longitude', $lng)
            ->select(
                'preference_location_id',
                'location_name',
                'latitude',
                'longitude',
                DB::raw('updated_at as created_at')
            )
            ->first();
    }

    public function getLocations($userId)
    {
        $rows = DB::table('user_preference_locations')
            ->where('user_id', $userId)
            ->select(
                'preference_location_id',
                'location_name',
                'latitude',
                'longitude',
                'created_at',
                'updated_at'
            )
            ->orderBy('updated_at', 'desc')
            ->get();

        return $rows->map(function ($row) {
            return (object) [
                'preference_location_id' => $row->preference_location_id,
                'location_name' => $row->location_name,
                'location' => [
                    (float) $row->latitude,
                    (float) $row->longitude,
                ],
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ];
        });
    }
}
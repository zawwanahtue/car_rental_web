<?php
namespace App\Services;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CommonService
{
    public function ForeignKeyIsExit($table, $column, $id)
    {
        return DB::table($table)->where($column, $id)->exists();
    }

    public function isExit($table, $column, $value)
    {
        return DB::table($table)->where($column, $value)->exists();
    }   

    public function generateTicketNumber()
    {
        $date = now()->format('Ymd');
        $time = now()->format('His');
        $ticketNumber = 'TCK-' . $date . '-' . $time;
        return $ticketNumber;
    }

    public function getTicketNumber()
    {
        do {
            $ticketNumber = $this->generateTicketNumber();
            $isExist = $this->isExit('bookings', 'ticket_number', $ticketNumber);
        } while ($isExist);

        return $ticketNumber;
    }

    public function timeStampTypeCaster($date, $time) {
        $dateTime = $date . ' ' . $time;
        return $dateTime;
    }

    public function passwordGenerate() {
        $password = Str::random(20);
        return $password;
    }

    public function haversine($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function getOfficeTimezone($officeId)
    {
        $office = DB::table('office_locations')
            ->where('office_location_id', $officeId)
            ->select('latitude', 'longitude')
            ->first();

        if (!$office) {
            return "Error: Office not found (ID: {$officeId})";
        }

        if ($office->latitude === null || $office->longitude === null) {
            return "Error: Office ID {$officeId} has invalid coordinates";
        }

        $lat = $office->latitude;
        $lng = $office->longitude;
        $key = env('TIMEZONEDB_API_KEY'); // fallback for testing

        $url = "https://api.timezonedb.com/v2.1/get-time-zone?key={$key}&format=json&by=position&lat={$lat}&lng={$lng}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,  // DEV ONLY
            CURLOPT_SSL_VERIFYHOST => false,  // DEV ONLY
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (CarRentalApp)', // Some APIs block blank
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4, // Force IPv4 (fixes DNS)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        // DEBUG: Log for investigation
        Log::info("Timezone API Call", [
            'url' => $url,
            'effective_url' => $effectiveUrl,
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'response' => $response
        ]);

        if ($httpCode === 0) {
            return "Error: cURL failed to connect. Check server firewall, SSL, or CA bundle.";
        }

        if ($httpCode !== 200) {
            return "Error: Timezone API failed (HTTP {$httpCode})";
        }

        if ($curlError) {
            return "Error: cURL error: {$curlError}";
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['status'])) {
            return "Error: Invalid JSON response";
        }

        if ($data['status'] !== 'OK') {
            return "Error: API error - " . ($data['message'] ?? 'Unknown');
        }

        if (!isset($data['zoneName'])) {
            return "Error: zoneName missing";
        }

        return $data['zoneName']; // "Europe/Paris"
    }
}
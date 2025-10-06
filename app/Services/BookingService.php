<?php
namespace App\Services;

use App\Services\FileService;
use App\Services\CommonService;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;

class BookingService
{
    public function __construct()
    {
    }

    public function getAllBookings()
    {
        $bookings = DB::table('bookings')->get();
        return $bookings;
    }

    public function getBookingsByUser($userId)
    {
        $bookings = DB::table('bookings')->where('user_id', $userId)->get();
        return $bookings;
    }

    public function createBooking($data)
    {
        $response = DB::table('bookings')->insert($data);
        if (!$response) {
            return "Failed to create booking.";
        } else {
            return null;
        }
    }

    // Booking Type
    public function getAllBookingTypes()
    {
        $bookingTypes = DB::table('booking_types')->get();
        return $bookingTypes;
    }

    public function createBookingType($data)
    {
        $response = DB::table('booking_types')->insert($data);
        if (!$response) {
            return "Failed to create booking type.";
        } else {
            return null;
        }
    }

    public function updateBookingType($data)
    {
        $response = DB::table('booking_types')->where('booking_type_id', $data['booking_type_id'])->update($data);
        if (!$response) {
            return "Booking type not found.";
        } else {
            return null;
        }
    }

    public function deleteBookingType($id)
    {
        $response = DB::table('booking_types')->where('booking_type_id', $id)->delete();
        if (!$response) {
            return "Booking type not found.";
        } else {
            return null;
        }
    }
}
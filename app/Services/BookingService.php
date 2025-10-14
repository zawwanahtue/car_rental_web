<?php
namespace App\Services;

use App\Services\FileService;
use App\Services\CommonService;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    protected $commonService;
    protected $carService;
    public function __construct(CommonService $commonService, CarService $carService)
    {
        $this->commonService = $commonService;
        $this->carService = $carService;
    }

    public function getAllBookings()
    {
        $bookings = DB::table('bookings')->get();
        if (!$bookings) {
            return "No bookings found.";
        }
        return $bookings;
    }

    public function getBookingsByUser()
    {
        $id = Auth::user()->user_id;
        $bookings = DB::table('bookings')->where('user_id', $id)->get();
        if (!$bookings) {
            return "No bookings found.";
        }
        return $bookings;
    }

    public function createBooking($data)
    {
        $data['ticket_number'] = $this->commonService->getTicketNumber();
        $data['user_id'] = Auth::user()->user_id;
        $data['booking_status'] = 'pending';
        $response = DB::table('bookings')->insert($data);
        if (!$response) {
            return "Failed to create booking.";
        } else {
            $response = DB::table('cars')->where('car_id', $data['car_id'])->update(['availability' => false]);
            if (!$response) {
                return "Failed to update car availability.";
            }
            return null;
        }
    }

    public function updateBooking($data)
    {
        (isset($data['car_id'])) ? $data['car_id'] : $data['car_id'] = null;
        $response = DB::table('bookings')->where('booking_id', $data['booking_id'])->update($data);
        if (!$response) {
            return "Failed to update booking.";
        } else {
            if (isset($data['car_id'])) {
                $response = DB::table('cars')->where('car_id', $data['car_id'])->update(['availability' => true]);
                if (!$response) {
                    return "Failed to update car availability.";
                }
            }
            return null;
        }
    }
}
<?php
namespace App\Services;

use App\Services\CommonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\OfficeLocationService;

class BookingService
{
    protected $commonService;
    protected $carService;
    protected $officeLocationService;

    public function __construct(CommonService $commonService, CarService $carService, OfficeLocationService $officeLocationService)
    {
        $this->commonService = $commonService;
        $this->carService = $carService;
        $this->officeLocationService = $officeLocationService;
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
        $pickupLocationData = [
            'latitude' => $data['pickup_latitude'],
            'longitude' => $data['pickup_longitude']
        ];
        $dropoffLocationData = [
            'latitude' => $data['dropoff_latitude'],
            'longitude' => $data['dropoff_longitude']
        ];
        $data['deliver_need'] = $this->officeLocationService->isOfficeLocation($pickupLocationData);
        $data['take_back_need'] = $this->officeLocationService->isOfficeLocation($dropoffLocationData);
        $data['ticket_number'] = $this->commonService->getTicketNumber();
        $data['user_id'] = Auth::user()->user_id;
        $data['booking_status'] = 'pending';
        $response1 = DB::table('cars')->where('car_id', $data['car_id'])->update(['availability' => false]);
        if (!$response1) {
            return "This car is not available. Please select another car.";
        }
        else {
            $response = DB::table('bookings')->insert($data);
            if (!$response) {
                return "Failed to create booking.";
            } else {
                return null;
            }
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

    public function getCustomerPickupBookings()
    {
        $bookings = DB::table('bookings')
            ->where('deliver_need', true)
            ->where('booking_status', 'pending')
            ->get();
        if (!$bookings) {
            return "No customer pickup bookings found.";
        }
        return $bookings;
    }

    public function cancelBooking($id, $user_id)
    {
        $booking = DB::table('bookings')
            ->where('booking_id', $id)
            ->where('user_id', $user_id)
            ->select('booking_status', 'car_id', 'user_id')
            ->first();

        if (!$booking) {
            return "Booking not found";
        }

        $status = $booking->booking_status;

        if ($status == 'pending' || $status == 'confirmed') {

            $response = DB::table('bookings')
                ->where('booking_id', $id)
                ->where('user_id', $user_id) 
                ->update(['booking_status' => 'cancelled']);

            if (!$response) {
                return "Failed to cancel booking.";
            }

            $car_id = $booking->car_id;
            $car_update_response = DB::table('cars')
                ->where('car_id', $car_id)
                ->update(['availability' => true]);

            if (!$car_update_response) {
                return "Booking cancelled, but failed to update car availability.";
            }
            
            if ($status == 'confirmed') { 
                $user_update_response = DB::table('users')
                    ->where('user_id', $booking->user_id)
                    ->increment('cancellation_count');

                if (!$user_update_response) {
                    return "Booking cancelled, but failed to update user's cancellation count.";
                }
            }
            
            return null;
        } 
        else {
            return "Only pending or confirmed bookings can be cancelled.";
        }
    }
}
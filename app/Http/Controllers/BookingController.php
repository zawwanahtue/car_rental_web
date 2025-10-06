<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingService;
use App\Helpers\Helper;
use App\Services\CommonService;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $bookingService;
    protected $helper;
    protected $commonService;

    public function __construct(BookingService $bookingService, Helper $helper, CommonService $commonService)
    {
        $this->bookingService = $bookingService;
        $this->helper = $helper;
        $this->commonService = $commonService;
    }

    public function getBookings()
    {
        $bookings = $this->bookingService->getAllBookings();
        return $this->helper->PostMan($bookings, 200, "Bookings Retrieved Successfully");
    }

    public function getBookingByUser($id)
    {
        $bookings = $this->bookingService->getBookingsByUser($id);
        return $this->helper->PostMan($bookings, 200, "User Bookings Retrieved Successfully");
    }

    public function createBooking(Request $request)
    {
        $rules = [
            'car_id' => 'required|integer|exists:cars,id',
            'booking_type_id' => 'required|integer|exists:booking_types,id',
            'start_datetime' => 'required|datetime|after:now',
            'end_datetime' => 'required|datetime|after:start_datetime',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data['user_id'] = Auth::user()->user_id;
            $data = $request->all();
            $response = $this->bookingService->createBooking($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Booking Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateBoooking(Request $request, $id)
    {
        // Logic to update a booking
        return response()->json(['message' => 'Booking updated successfully'], 200);
    }

    public function deleteBooking($id)
    {
        // Logic to delete a booking
        return response()->json(['message' => 'Booking deleted successfully'], 200);
    }


    public function cancelBooking(Request $request, $id)
    {
        // Logic to cancel a booking
        return response()->json(['message' => 'Booking cancelled successfully'], 200);
    }

    // Booking Type
    public function getBookingType()
    {
        $bookingTypes = $this->bookingService->getAllBookingTypes();
        return $this->helper->PostMan($bookingTypes, 200, "Booking Types Retrieved Successfully");
    }

    public function createBookingType(Request $request)
    {
        $rules = [
            'type_name' => 'required|string|max:255|unique:booking_types,type_name',
            'description' => 'required|string|max:500',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data = $request->all();
            $response = $this->bookingService->createBookingType($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Booking Type Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateBookingType(Request $request, $id)
    {
        $rule = [
            'type_name' => 'nullable|string|max:255|unique:booking_types,type_name,' . $id . ',booking_type_id',
            'description' => 'nullable|string|max:500',
        ];
        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate)) {
            $data = $request->all();
            $data['booking_type_id'] = $id;
            $response = $this->bookingService->updateBookingType($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 200, "Booking Type Successfully Updated");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteBookingType($id)
    {
        $isRelated = $this->commonService->ForeignKeyIsExit("bookings", "booking_type_id", $id);
        if ($isRelated) {
            return $this->helper->PostMan(null, 404, "Booking type have used. Cannot delete.");
        }
        $bookingType = $this->bookingService->deleteBookingType($id);
        if (is_null($bookingType))
        {
            return $this->helper->PostMan(null, 200, "Booking Type Successfully Deleted");
        } else {
            return $this->helper->PostMan(null, 500, $bookingType);
        }
    }
}
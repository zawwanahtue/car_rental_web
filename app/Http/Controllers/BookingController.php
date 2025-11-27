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

    public function getBookings(Request $request)
    {
        $rules = [
            'search_by'     => 'nullable|string|max:255',
            'first'         => 'required|integer|min:1',
            'max'           => 'required|integer|min:1',
            'filter_by'     => 'nullable|string|in:pending,confirmed,cancelled,completed,needs_delivery,needs_takeback',
            'status'        => 'nullable|string|in:pending,confirmed,cancelled,completed',
            'delivery'      => 'nullable|in:0,1',
            'takeback'      => 'nullable|in:0,1',
            'office_id'     => 'nullable|integer|exists:office_locations,office_location_id',
            'car_type_id'   => 'nullable|integer|exists:car_type,car_type_id',
            'date_from'     => 'nullable|date',
            'date_to'       => 'nullable|date|after_or_equal:date_from',
            'sort_by'       => 'nullable|string|in:created_at,pickup_datetime,total_amount,average_rating',
            'sort'          => 'nullable|string|in:asc,desc'
        ];

        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) {
            $data = $request->all();
            $user = $request->user();

            if (!in_array($user->user_type_id, [2, 3])) {
                return $this->helper->PostMan(null, 403, "Forbidden");
            }

            $response = $this->bookingService->getAllBookings($data, $user);
            return $this->helper->PostMan($response, 200, "Bookings retrieved successfully");
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function getBookingByUser(Request $request)
    {
        $rules = [
            'first' => 'nullable|integer|min:1',
            'max'   => 'nullable|integer|min:1|max:100',
        ];

        $validate = $this->helper->validate($request, $rules);
        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $data = $request->all();
        $response = $this->bookingService->getBookingsByUser($data);

        return $this->helper->PostMan($response, 200, "User Bookings Retrieved Successfully");
    }

    public function createBooking(Request $request)
    {
        $pickup_datetime = $this->commonService->timeStampTypeCaster($request->pickup_date, $request->pickup_time);
        $dropoff_datetime = $this->commonService->timeStampTypeCaster($request->dropoff_date, $request->dropoff_time);
        $request->merge(['pickup_datetime' => $pickup_datetime, 'dropoff_datetime' => $dropoff_datetime]);

        $rules = [
            'car_id' => 'required|integer|exists:cars,car_id',
            'pickup_datetime' => 'required|date|after_or_equal:now',
            'dropoff_datetime' => 'required|date|after:pickup_datetime',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
            'total_amount' => 'required|numeric|min:0',
        ];

        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) {
            $data = $request->only([
                'car_id', 'pickup_datetime', 'dropoff_datetime',
                'pickup_latitude', 'pickup_longitude',
                'dropoff_latitude', 'dropoff_longitude',
                'total_amount'
            ]);

            $response = $this->bookingService->createBooking($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Booking Successfully Created");
            } else {
                return $this->helper->PostMan(null, 400, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function cancelBooking($id)
    {
        $user_id = Auth::user()->user_id;
        $response = $this->bookingService->cancelBooking($id, $user_id);
        if (is_null($response)) {
            return $this->helper->PostMan(null, 200, "Booking Successfully Cancelled");
        } else {
            return $this->helper->PostMan(null, 400, $response);
        }
    }

    public function getTodayDeliveries(Request $request)
    {
        $rules = ['office_id' => 'required|integer|exists:office_locations,office_location_id'];
        $validate = $this->helper->validate($request, $rules);

        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $deliveries = $this->bookingService->getCustomerPickupBookings($request->office_id);
        return $this->helper->PostMan($deliveries, 200, "Today's deliveries retrieved");
    }

    public function getTodayTakeBacks(Request $request)
    {
        $rules = ['office_id' => 'required|integer|exists:office_locations,office_location_id'];
        $validate = $this->helper->validate($request, $rules);

        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $takebacks = $this->bookingService->getCustomerTakebackBookings($request->office_id);
        return $this->helper->PostMan($takebacks, 200, "Today's take-backs retrieved");
    }

    // ===================================================================
    // NEW STAFF TASK ENDPOINTS — FULLY WORKING
    // ===================================================================

    public function claimDelivery($booking_id)
    {
        $result = $this->bookingService->claimDeliveryTask($booking_id, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Delivery task claimed successfully")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function claimTakeback($booking_id)
    {
        $result = $this->bookingService->claimTakebackTask($booking_id, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Take-back task claimed successfully")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function myActiveTasks()
    {
        $tasks = $this->bookingService->getMyActiveTasks(Auth::id());
        return $this->helper->PostMan($tasks, 200, "Your active tasks retrieved");
    }

    public function staffTaskHistory()
    {
        $history = $this->bookingService->getStaffTaskHistory(Auth::id());
        return $this->helper->PostMan($history, 200, "Task history retrieved");
    }

    public function getMaintenanceTasks()
    {
        $staffId = Auth::id();
        $tasks = $this->bookingService->getMaintenanceTaskHistory($staffId);
        return $this->helper->PostMan($tasks, 200, "Maintenance tasks retrieved");
    }

    // 4. COMPLETE DELIVERY — simple body
    public function completeDelivery(Request $request, $task_id)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0'  // optional fine
        ]);

        $result = $this->bookingService->completeDelivery([
            'task_id'     => $task_id,
            'staff_id'    => Auth::id(),
            'amount_paid' => $request->amount_paid,
            'fine_amount' => $request->fine_amount ?? 0
        ]);

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Delivery completed & payment recorded")
            : $this->helper->PostMan(null, 400, $result);
    }

    // 5. COMPLETE TAKEBACK — NO BODY NEEDED
    public function completeTakeback($task_id)
    {
        $result = $this->bookingService->completeTakeback($task_id, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Take-back completed & car returned")
            : $this->helper->PostMan(null, 400, $result);
    }

    // BookingController.php

    public function reportDamage(Request $request)
    {
        $rules = [
            'car_id'      => 'required|integer|exists:cars,car_id',
            'description' => 'required|string|max:1000',
            'cost'        => 'nullable|numeric|min:0'
        ];

        $validate = $this->helper->validate($request, $rules);
        if (!is_null($validate)) {
            return $this->helper->PostMan(null, 422, $validate);
        }

        $result = $this->bookingService->reportDamage($request, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Damage reported & maintenance task created")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function completeMaintenance($maintenance_id)
    {
        $result = $this->bookingService->completeMaintenance($maintenance_id, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Car fixed & back in service")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function adminRevenueDashboard()
    {
        $service = $this->bookingService->getAdminDashboardData();

        return $this->helper->PostMan($service, 200, "Dashboard loaded");
    }

    public function doTheStaffEarly()
    {
        $isEarly = $this->bookingService->doTheStaffEarly(Auth::id());

        if($isEarly === true)
        {
            return $this->helper->PostMan(true, 200, "You are early today");
        }
        else
        {
            return $this->helper->PostMan($isEarly, 200, "You have tasks today");
        }
    }

    public function costByTicketNumber($ticketNumber)
    {
        $costs = $this->bookingService->costByTicket($ticketNumber);

        if (is_null($costs)) {
            return $this->helper->PostMan(null, 404, "No booking found for the provided ticket number");
        }

        return $this->helper->PostMan($costs, 200, "Costs calculated successfully");
    }

    public function getTodaySelfPickups()
    {
        $list = $this->bookingService->getTodaySelfPickups();
        return $this->helper->PostMan($list, 200, "Today's self pickups (all offices)");
    }

    public function getTodaySelfDropoffs()
    {
        $list = $this->bookingService->getTodaySelfDropoffs();
        return $this->helper->PostMan($list, 200, "Today's self dropoffs (all offices)");
    }

    public function completeSelfPickup(Request $request)
    {
        $request->validate([
            'booking_id'   => 'required|integer|exists:bookings,booking_id',
            'amount_paid'  => 'required|numeric|min:0',
            'fine_amount'  => 'nullable|numeric|min:0'
        ]); 

        $result = $this->bookingService->completeSelfPickup(
            $request->booking_id,
            Auth::id(),
            $request->amount_paid,
            $request->fine_amount ?? 0
        );

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Self pickup completed successfully")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function completeSelfDropoff(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,booking_id'
        ]);

        $result = $this->bookingService->completeSelfDropoff(
            $request->booking_id,
            Auth::id()
        );

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Self dropoff completed successfully")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function noShowDelivery(Request $request)
    {
        $request->validate(['booking_id' => 'required|integer|exists:bookings,booking_id']);

        $result = $this->bookingService->markNoShowDelivery($request->booking_id, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Customer marked as no-show (delivery)")
            : $this->helper->PostMan(null, 400, $result);
    }

    public function noShowSelfPickup(Request $request)
    {
        $request->validate(['booking_id' => 'required|integer|exists:bookings,booking_id']);

        $result = $this->bookingService->markNoShowSelfPickup($request->booking_id, Auth::id());

        return is_null($result)
            ? $this->helper->PostMan(null, 200, "Customer marked as no-show (self-pickup)")
            : $this->helper->PostMan(null, 400, $result);
    }
}
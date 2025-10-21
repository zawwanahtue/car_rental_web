<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CarService;
use App\Helpers\Helper;
use App\Services\CommonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    protected $carService;
    protected $helper;
    protected $commonService;

    public function __construct(CarService $carService, Helper $helper, CommonService $commonService)
    {
        $this->carService = $carService;
        $this->helper = $helper;
        $this->commonService = $commonService;
    }

    ///Car Type
    public function carTypes()
    {
        $carTypes = $this->carService->carTypes();
        return $this->helper->PostMan($carTypes, 200, "Car Types Retrieved Successfully");
    }

    public function createCarType(Request $request)
    {
        $rules = [
            'type_name' => 'required|string|max:255|unique:car_type,type_name',
            'description' => 'required|string|max:500',
            'car_type_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate)) 
        {
            $data = $request->all();
            $response = $this->carService->createCarType($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Car Type Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } 
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateCarType(Request $request, $id)
    {
        $rules = [
            'type_name' => 'nullable|string|max:255|unique:car_type,type_name,' . $id . ',car_type_id',
            'description' => 'nullable|string|max:500',
            'car_type_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ];
        $validate = $this->helper->validate($request, $rules);
        if (is_null($validate))
        {            
            $data = $request->all();
            $data['id'] = $id; 
            $response = $this->carService->updateCarType($data);
            if (is_null($response)) {
                $currentCarType = $this->carService->getCarTypeById($id);
                return $this->helper->PostMan($currentCarType, 200, "Car Type Successfully Updated");
            }
            else 
            {
                return $this->helper->PostMan(null, 500, $response);
            }
        }
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteCarType($id)
    {
        $isRelated = $this->commonService->ForeignKeyIsExit("cars", "car_type_id", $id);
        if ($isRelated) {
            return $this->helper->PostMan(null, 404, "Car type have used. Cannot delete.");
        }
        $carType = $this->carService->deleteCarType($id);
        if (is_null($carType)) {
            return $this->helper->PostMan(null, 200, "Car type deleted successfully");
        }
        return $this->helper->PostMan(null, 404, $carType);
    }

    ///Car
    public function getCars(Request $request)
    {
        // Validation rules
        $rules = [
            'first' => 'required|integer|min:1',
            'max' => 'required|integer|min:1',
            'pickup_datetime'  => 'nullable|date|required_with:dropoff_datetime',
            'dropoff_datetime' => 'nullable|date|required_with:pickup_datetime',
            'asc_total' => 'nullable|string|in:false,true',
            'asc_hour'  => 'nullable|string|in:false,true',
            'asc_day'   => 'nullable|string|in:false,true',
            'car_type_id' => 'nullable|integer|exists:car_type,car_type_id',
            'fuel_type'   => 'nullable|string|in:petrol,diesel,electric',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Custom rules
        $validator->after(function ($validator) use ($request) {
            $ascFields = ['asc_total', 'asc_hour', 'asc_day'];
            $filledAsc = array_filter($ascFields, fn($f) => !empty($request->$f));

            // Only one asc field allowed
            if (count($filledAsc) > 1) {
                $validator->errors()->add('asc','Only one of asc_total, asc_hour, or asc_day can be provided.');
            }

            // If asc_total is provided → require pickup/dropoff
            if (!empty($request->asc_total) && $request->asc_total !== null) {
                if (empty($request->pickup_datetime) || empty($request->dropoff_datetime)) {
                    $validator->errors()->add('date', 'pickup_datetime and dropoff_datetime are required when sorting by total_price.');
                }
            }
        });

        // Check validation
        if ($validator->fails()) {
            return $this->helper->PostMan(null, 422, $validator->errors()->first());
        }

        // Calculate total rental time in hours
        $totalHours = null;
        if (!empty($request->pickup_datetime) && !empty($request->dropoff_datetime)) {
            $pickup  = Carbon::parse($request->pickup_datetime);
            $dropoff = Carbon::parse($request->dropoff_datetime);
            $totalHours = round($pickup->floatDiffInHours($dropoff), 2);
        }

        $data = $request->all();
        $data['total_hours'] = $totalHours;

        $response = $this->carService->getCars($data);

        return $this->helper->PostMan($response, 200, "Cars Retrieved Successfully");
    }

    public function addCar(Request $request)
    {
        $rule = [
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|unique:cars,license_plate',
            'car_type_id' => 'required|integer|exists:car_type,car_type_id',
            'price_per_hour' => 'required|numeric|min:0',
            'price_per_day' => 'required|numeric|min:0',
            'number_of_seats' => 'required|integer|min:1',
            'luggage_capacity' =>   'required|integer|min:0',
            'color' => 'required|string|max:50',
            'transmission' => 'required|string|in:auto,manual',
            'fuel_type' =>  'required|string|in:petrol,diesel,electric',
            'car_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'owner_id' => 'sometimes|integer|exists:owners,owner_id|nullable',
            'ownership_condition' => 'required|string|in:company_owned,external_owned',
        ];

        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate))
        {
            $data = $request->all();
            // dd($data);
            $response = $this->carService->addCar($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Car Successfully Added");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        }
        else 
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateCar(Request $request, $id)
    {
        $rule = [
            'model' => 'nullable|string|max:255',
            'license_plate' => 'nullable|string|unique:cars,license_plate',
            'car_type_id' => 'nullable|integer|exists:car_type,car_type_id',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'number_of_seats' => 'nullable|integer|min:1',
            'luggage_capacity' =>   'nullable|integer|min:0',
            'color' => 'nullable|string|max:50',
            'transmission' => 'nullable|string|in:auto,manual',
            'fuel_type' =>  'nullable|string|in:petrol,diesel,electric',
            'car_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'owner_id' => 'nullable|integer|exists:owners,owner_id',
            'ownership_condition' => 'nullable|string|in:company_owned,external_owned',
        ];  

        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate))
        {
            $data = $request->all();
            $data['id'] = $id; 
            $response = $this->carService->updateCar($data);
            if (is_null($response)) {
                return $this->helper->PostMan($response, 200, "Car Updated Successfully");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        }
        else
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteCar($id)
    {
        $isRelated = $this->commonService->ForeignKeyIsExit("bookings", "car_id", $id);
        $isRelated1 = $this->commonService->ForeignKeyIsExit("maintenance", "car_id", $id);
        if ($isRelated || $isRelated1) {
            return $this->helper->PostMan(null, 404, "Car have used. Cannot delete.");
        }
        $car = $this->carService->deleteCar($id);
        if (is_null($car)) {
            return $this->helper->PostMan(null, 200, "Car deleted successfully");
        }
        return $this->helper->PostMan(null, 404, $car);
    }
}

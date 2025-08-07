<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CarService;
use App\Helpers\Helper;

class CarController extends Controller
{
    protected $carService;
    protected $helper;

    public function __construct(CarService $carService, Helper $helper)
    {
        $this->carService = $carService;
        $this->helper = $helper;
    }

    // public function createCar()
    // {
    //     // Logic to create a car
    //     return response()->json(['message' => 'Car created successfully'], 201);
    // }

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

    public function carTypeById($id)
    {
        $carType = $this->carService->getCarTypeById($id);
        if ($carType) {
            return $this->helper->PostMan($carType, 200, "Car Type Retrieved Successfully");
        } else {
            return $this->helper->PostMan(null, 404, "Car Type Not Found");
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

    public function deleteCarType($id = 11)
    {
        $carType = $this->carService->deleteCarType($id);
        if (is_null($carType)) {
            return $this->helper->PostMan(null, 200, "Car type deleted successfully");
        }
        return $this->helper->PostMan(null, 404, $carType);
    }

    ///Car
    public function listCars()
    {
        $cars = $this->carService->listCars();
        return $this->helper->PostMan($cars, 200, "Cars Retrieved Successfully");
    }

    public function getCarById($id)
    {
        $car = $this->carService->getCarById($id);
        if ($car) {
            return $this->helper->PostMan($car, 200, "Car Retrieved Successfully");
        } else {
            return $this->helper->PostMan(null, 404, "Car Not Found");
        }
    }

    public function addCar(Request $request)
    {
        $rule = [
            'car_model' => 'required|string|max:255',
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
        ];

        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate))
        {
            $data = $request->all();
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

    public function updateCar($id)
    {
        $car = $this->carService->updateCar($id);
        if ($car) {
            return $this->helper->PostMan($car, 200, "Car Retrieved Successfully");
        } else {
            return $this->helper->PostMan(null, 404, "Car Not Found");
        }
    }
}

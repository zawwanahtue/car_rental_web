<?php
namespace App\Services;

use App\Services\FileService;
use App\Services\CommonService;
use Illuminate\Support\Facades\DB;

class CarService
{
    protected $fileService;
    protected $commonService;
    public function __construct(FileService $fileService, CommonService $commonService)
    {
        $this->fileService = $fileService;
        $this->commonService = $commonService;
    }

    ///Car Type
    public function carTypes()
    {
        $carTypes = DB::table('car_type as ct')
            ->leftJoin('photo_paths as pp', 'ct.photo_path_id', '=', 'pp.photo_path_id')
            ->select(
                'ct.car_type_id', 
                'ct.type_name',
                'ct.description',
                DB::raw("CONCAT('" . env('R2_URL') . "/', pp.photo_path) as car_type_image_url")
            )
            ->get();
        return $carTypes;
    }

    public function createCarType(array $data)
    {
        $photoPath = $this->fileService->uploadFile($data['car_type_image'], 'Car_Types/');
        if (!$photoPath) {
            return "Failed to upload car type image.";
        }

        $carTypeId = DB::table('car_type')->insertGetId([
            'type_name' => $data['type_name'],
            'description' => $data['description'],
            'photo_path_id' => DB::table('photo_paths')->insertGetId(['photo_path' => $photoPath]),
        ]);

        return $carTypeId ? null : "Failed to create car type.";
    }

    public function getCarTypeById($id)
    {
        $carType = DB::table('car_type as ct')
            ->leftJoin('photo_paths as pp', 'ct.photo_path_id', '=', 'pp.photo_path_id')
            ->select(
                'ct.car_type_id', 
                'ct.type_name',
                'ct.description',
                DB::raw("CONCAT('" . env('R2_URL') . "/', pp.photo_path) as car_type_image_url")
            )
            ->where('ct.car_type_id', $id)
            ->first();

        return $carType;
    }

    public function alreadyExistsImagePath($id)
    {
        $photoPath = DB::table('photo_paths')
        ->where('photo_path_id', $id)
        ->value('photo_path');
        return $photoPath;
    }

    public function updateCarType($data)
    {
        $carType = DB::table('car_type')
            ->where('car_type_id', $data['id'])
            ->first();
        if (!$carType) {
            return "Car type not found.";
        }

        if (isset($data['car_type_image'])) {
            $existsPhotoPath=$this->alreadyExistsImagePath($carType->photo_path_id);
            $photoDelete = $this->fileService->deleteFile($existsPhotoPath);
            if (!$photoDelete) {
                return "Failed to delete old car type image.";
            }
            $photoPath = $this->fileService->uploadFile($data['car_type_image'], 'Car_Types/');
            if (!$photoPath) {
                return "Failed to upload car type image.";
            }
            DB::table('photo_paths')
            ->where('photo_path_id', $carType->photo_path_id)
            ->update(
                ['photo_path' => $photoPath],
                ['updated_at' => now()]
            );
        }

        DB::table('car_type')->where('car_type_id', $data['id'])->update([
            'type_name' => $data['type_name'] ?? $carType->type_name,
            'description' => $data['description'] ?? $carType->description,
            'updated_at' => now(),
        ]);

        return null; 
    }

    public function deleteCarType($id)
    {
        $carType = DB::table('car_type')->where('car_type_id', $id)->first();
        if (!$carType) {
            return "Car type not found.";
        }

        $existsPhotoPath = $this->alreadyExistsImagePath($carType->photo_path_id);
        if ($existsPhotoPath) {
            $deletePhoto = $this->fileService->deleteFile($existsPhotoPath);
            if (!$deletePhoto) {
                return "Failed to delete car type image.";
            }
        }
        // Delete the photo path record
        DB::table('car_type')->where('car_type_id', $id)->delete();
        DB::table('photo_paths')->where('photo_path_id', $carType->photo_path_id)->delete();

        return null; 
    }
    
    ///Car
    public function getCars($data)
    { 
        $query = DB::table('cars as c')
            ->leftJoin('car_type as ct', 'c.car_type_id', '=', 'ct.car_type_id')
            ->leftJoin('photo_paths as pp', 'c.photo_path_id', '=', 'pp.photo_path_id')
            ->leftJoin('owners as o', 'c.owner_id', '=', 'o.owner_id')
            ->select(
                'c.car_id',
                'ct.type_name as car_type',
                'c.model',
                'c.license_plate',
                'c.price_per_hour',
                'c.price_per_day',
                'c.availability',
                'c.number_of_seats',
                'c.luggage_capacity',
                'c.color',
                'c.transmission',
                'c.fuel_type',
                'c.ownership_condition',
                'o.name as owner_name',
                'c.created_at',
                'c.updated_at',
                DB::raw("CONCAT('" . env('R2_URL') . "/', pp.photo_path) as car_image_url")
            )
            ->where('c.availability', true); // only available cars

        // Filtering
        if (!empty($data['car_type_id'])) {
            $query->where('c.car_type_id', $data['car_type_id']);
        }

        if (!empty($data['fuel_type'])) {
            $query->where('c.fuel_type', $data['fuel_type']);
        } 

        // Sorting
        if (isset($data['asc'])) { // Use isset since `asc` can be explicitly false (0) 
            $direction = !$data['asc'] ? 'desc' : 'asc';
            $query->orderBy('c.price_per_day', $direction);
        }

        $totalCars = DB::table('cars')->where('availability', true)->count();

        // Pagination
        $page = max(1, (int)$data['first']); // page number
        $max = max(1, (int)$data['max']);    // items per page
        $offset = ($page - 1) * $max;

        $cars = $query->offset($offset)->limit($max)->get();

        return [
            'cars' => $cars,
            'totalCars' => $totalCars
        ];
    }


    public function addCar(array $data)
    {
        $photoPath = $this->fileService->uploadFile($data['car_image'], 'Cars/');
        if (!$photoPath) {
            return "Failed to upload car image.";
        }

        $carId = DB::table('cars')->insertGetId([
            'car_type_id' => $data['car_type_id'],
            'model' => $data['model'],
            'license_plate' => $data['license_plate'],
            'price_per_hour' => $data['price_per_hour'],
            'price_per_day' => $data['price_per_day'],
            'availability' => true,
            'number_of_seats' => $data['number_of_seats'],
            'luggage_capacity' => $data['luggage_capacity'],
            'color' => $data['color'],
            'transmission' => $data['transmission'],
            'fuel_type' => $data['fuel_type'],
            'photo_path_id' => DB::table('photo_paths')->insertGetId(['photo_path' => $photoPath]),
            'owner_id' => $data['owner_id'],
            'ownership_condition' => $data['ownership_condition'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $carId ? null : "Failed to create car.";
    }

    public function updateCar($data)
    {
        $car = DB::table('cars')->where('car_id', (int)$data['id'])->first();
        if (!$car) {
            dd($car, $data['id']);
            return "Car not found.";
        }


        if (isset($data['car_image'])) {
            $existsPhotoPath = $this->alreadyExistsImagePath($car->photo_path_id);
            $photoDelete = $this->fileService->deleteFile($existsPhotoPath);
            if (!$photoDelete) {
                return "Failed to delete old car image.";
            }
            $photoPath = $this->fileService->uploadFile($data['car_image'], 'Cars/');
            if (!$photoPath) {
                return "Failed to upload car image.";
            }
            DB::table('photo_paths')
                ->where('photo_path_id', $car->photo_path_id)
                ->update(['photo_path' => $photoPath, 'updated_at' => now()]);
        }

        DB::table('cars')->where('car_id', $data['id'])->update([
            'car_type_id' => $data['car_type_id'] ?? $car->car_type_id,
            'model' => $data['car_model'] ?? $car->model,
            'license_plate' => $data['license_plate'] ?? $car->license_plate,
            'price_per_hour' => $data['price_per_hour'] ?? $car->price_per_hour,
            'price_per_day' => $data['price_per_day'] ?? $car->price_per_day,
            'availability' => isset($data['availability']) ? (bool)$data['availability'] : $car->availability,
            'number_of_seats' => $data['number_of_seats'] ?? $car->number_of_seats,
            'luggage_capacity' => $data['luggage_capacity'] ?? $car->luggage_capacity,
            'color' => $data['color'] ?? $car->color,
            'transmission' => $data['transmission'] ?? $car->transmission,
            'fuel_type' => $data['fuel_type'] ?? $car->fuel_type,
        ]);

        return null; 
    }

    public function deleteCar($id)
    {
        $car = DB::table('cars')->where('car_id', $id)->first();
        if (!$car) {
            return "Car not found.";
        }
        $existsPhotoPath = $this->alreadyExistsImagePath($car->photo_path_id);
        $photoDelete = $this->fileService->deleteFile($existsPhotoPath);
        if (!$photoDelete) {
            return "Failed to delete old car image.";
        }
        DB::table('cars')->where('car_id', $car->car_id)->delete();
        DB::table('photo_paths')->where('photo_path_id', $car->photo_path_id)->delete();
        return null;
    }

    public function isCarAvailable($id)
    {
        $car = DB::table('cars')->where('car_id', $id)->first();
        if (!$car) {
            return "Car not found.";
        }
        $isCarAvailable = $car->availability;
        if ($isCarAvailable == false) {
            return "Selected car is not available. Please select another car.";
        }
        return null;
    }
}
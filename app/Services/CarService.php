<?php
namespace App\Services;

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;

class CarService
{
    protected $fileService;
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }


    // // This service will handle car-related business logic
    // public function createCar(array $data)
    // {
    //     // Logic to create a car in the database
    //     // For example, using Eloquent to save a new Car model
    //     // return Car::create($data);
        
    //     // Placeholder return for now
    //     return true;
    // }

    // public function getCarById($id)
    // {
    //     // Logic to retrieve a car by its ID
    //     // return Car::find($id);
        
    //     // Placeholder return for now
    //     return null;
    // }


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

        $carTypeId = DB::table('car_type')
        ->insertGetId([
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

    public function updateCarType($data)
    {
        $carType = DB::table('car_type')
            ->where('car_type_id', $data['id'])
            ->first();
        if (!$carType) {
            return "Car type not found.";
        }

        DB::table('car_type')->where('car_type_id', $data['id'])->update([
            'type_name' => $data['type_name'] ?? $carType->type_name,
            'description' => $data['description'] ?? $carType->description,
            'photo_path_id' => isset($data['car_type_image']) ? DB::table('photo_paths')->insertGetId(['photo_path' => $this->fileService->uploadFile($data['car_type_image'], 'Car_Types/')]) : $carType->photo_path_id,
        ]);

        return null; 
    }
}
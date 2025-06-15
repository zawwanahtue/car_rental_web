<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\FileService;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;

class UserController extends Controller
{
    protected $userService;
    protected $fileService;
    protected $helper;

    public function __construct(UserService $userService, FileService $fileService, Helper $helper)
    {
        $this->userService = $userService;
        $this->fileService = $fileService;
        $this->helper = $helper;
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\+?[1-9]\d{9,14}$/',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];

        $validate = $this->helper->Validate($request, $rules);
        if(is_null($validate))
        {
            $data = $request->all();
            $response = $this->userService->register($data);
                
            if(is_null($response))
            {
                return $this->helper->PostMan(null, 201, "User Account Successfully Created");
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

    public function login(Request $request)
    {
        $rules=[
            'email' => 'required|email',
            'password' => 'required'
        ];
        $validate = $this->helper->Validate($request, $rules);
        if (is_null($validate))
        {
            $result = $this->userService->login($request->only('email', 'password'));

            if (!$result) {
                return $this->helper->PostMan(null, 401, "Invlid Credential");
            }

            return $this->helper->PostMan($result, 200, "Successfully Logined");
        }
        else
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function currentUser()
    {
        $user_id=Auth::user()->user_id;
        return $this->userService->currentUser($user_id);
    }

    public function profile()
    {
        return $this->helper->PostMan($this->currentUser(), 200, "Successfully Updated User");
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->helper->PostMan(null, 200, "Logged out successfully");
    }

    // public function uploadFile($photo)
    // {
    //     $file = $photo->file('image');
    //     $path = 'ProfileImages/';
    //     if (!$file->isValid()) { 
    //         return response()->json(['message' => 'Invalid file upload'], 400);
    //     }

    //     $result = $this->fileService->uploadImage($file, $path);    

    //     return response()->json(['url' => $result], 200);
    // }

    public function updateUser(Request $request)
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|regex:/^\+?[1-9]\d{9,14}$/',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . Auth::user()->user_id . ',user_id',
            'address' => 'sometimes|string|max:255',
            'image' => 'sometimes|file|max:10240',
        ];

        $validate = $this->helper->Validate($request, $rules);
        if (is_null($validate))
        {
            try {
                $data = $request->all();
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $photoPath = $this->userService->profileImageUpload($file); // ✅ directly call service
                    $data['photo_path'] = $photoPath;
                }

                $this->userService->updateUser($data);

                return $this->helper->PostMan($this->currentUser(), 200, "Successfully Updated User");
            } catch (\Exception $e) {
                return $this->helper->PostMan(null, 400, $e->getMessage());
            }
        }
        else
        {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function profileImageUpload(Request $request)
    {
        $file_path = $this->userService->profileImageUpload($request->file('image'));
        return $file_path;
    }
}
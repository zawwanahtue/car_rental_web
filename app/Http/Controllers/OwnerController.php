<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\Helper;
use App\Services\OwnerService;

class OwnerController extends Controller
{
    protected $helper;
    protected $ownerService;

    public function __construct(Helper $helper, OwnerService $ownerService) {
        $this->helper = $helper;
        $this->ownerService = $ownerService;
    }

    public function getOwner() {
        $owners = $this->ownerService->getAllOwners();
        if(is_null($owners)) {
            return $this->helper->PostMan(null, 404, "Owners Not Found");
        }
        return $this->helper->PostMan($owners, 200, "Owners Retrieved Successfully");
    }

    public function createOwner(Request $request) {
        $rule = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'required|string|email|max:255|unique:owners,email',
        ];
        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate)) {
            $data = $request->all();
            $response = $this->ownerService->createOwner($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Owner Successfully Created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function updateOwner(Request $request, $id) {
        $rule = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'required|string|email|max:255|unique:owners,email,' . $id . ',owner_id',
        ];
        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate)) {
            $data['owner_id'] = $id;
            $data = $request->all();
            $response = $this->ownerService->updateOwner($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Owner Successfully Updated");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }

    public function deleteOwner($id) {
        $response = $this->ownerService->deleteOwner($id);
        if (is_null($response)) {
            return $this->helper->PostMan(null, 201, "Owner Successfully Deleted");
        } else {
            return $this->helper->PostMan(null, 500, $response);
        }
    }
}
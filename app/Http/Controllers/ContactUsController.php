<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\CommonService;
use App\Services\ContactUsService;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    protected $contactUsService;
    protected $helper;
    protected $commonService;

    public function __construct(ContactUsService $contactUsService, Helper $helper, CommonService $commonService)
    {
        $this->helper = $helper;
        $this->commonService = $commonService;
        $this->contactUsService = $contactUsService;
    }

    public function getContactUs() {
        $response = $this->contactUsService->getContactUs();
        return $this->helper->PostMan($response, 200, "Contact us retrieved successfully");
    }

    public function createContactUs(Request $request) {
        $rule = [
            'title' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string',
            'description' => 'required|string'
        ];
        $validate = $this->helper->validate($request, $rule);
        if (is_null($validate)) {
            $data = $request->all();
            $response = $this->contactUsService->createContactUs($data);
            if (is_null($response)) {
                return $this->helper->PostMan(null, 201, "Contact us successfully created");
            } else {
                return $this->helper->PostMan(null, 500, $response);
            }
        } else {
            return $this->helper->PostMan(null, 422, $validate);
        }
    }
}